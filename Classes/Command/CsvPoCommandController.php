<?php

namespace Sitegeist\CsvPO\Command;

use League\Csv\Reader;
use League\Csv\Writer;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\CsvPO\Domain\TranslationOverrideRepository;
use Sitegeist\CsvPO\Domain\TranslationOverride;
use Sitegeist\CsvPO\Domain\TranslationLabelSource;
use Sitegeist\CsvPO\Domain\TranslationLabelSourceRepository;
use Neos\Flow\I18n\Service as LocalizationService;
use Neos\Flow\I18n\Locale;

class CsvPoCommandController extends CommandController
{
    /**
     * @var TranslationOverrideRepository
     * @Flow\Inject
     */
    protected $translationOverrideRepository;

    /**
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationLabelSourceRepository;

    /**
     * @var string[]
     * @Flow\InjectConfiguration(path="management.locales")
     */
    protected $locales;

    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localizationService;

    /**
     * Show a list of all translation sources
     */
    public function listCommand(): void
    {
        $allSources = $this->translationLabelSourceRepository->findAll();
        $rows = [];
        foreach ($allSources as $source) {
            $rows[] = [$source->getTitle(), $source->getIdentifier()];
        }
        $this->output->outputTable($rows, ['Title', 'Identifier']);
    }

    /**
     * Show all translations
     *
     * @param string|null $identifier the identifier to show (globbing is supported)
     */
    public function showAllCommand(?string $identifier = null): void
    {
        $allSources = $this->translationLabelSourceRepository->findAll();
        foreach ($allSources as $source) {
            $sourceIdentifier = $source->getIdentifier();
            if (!is_null($identifier) && $identifier != $sourceIdentifier) {
                continue;
            }
            $this->renderSource($source);
        }

        $this->output->outputLine('Legend:');
        $this->output->outputLine('- Translation');
        $this->output->outputLine('- <comment>O::Override</comment>');
        $this->output->outputLine('- <info>F::Fallback</info>');
    }

    /**
     * Show the translations of the specified source
     *
     * @param string $identifier the identifier to show (globbing is supported)
     */
    public function showCommand(string $identifier): void
    {
        $source = $this->translationLabelSourceRepository->findOneByIdentifier($identifier);
        if ($source) {
            $this->renderSource($source);
        }

        $this->output->outputLine('Legend:');
        $this->output->outputLine('- Translation');
        $this->output->outputFormatted('- <comment>O::Override</comment>');
        $this->output->outputLine('- <info>F::Fallback</info>');
    }

    /**
     * Bake the all translation overrides to the csv files
     *
     */
    public function bakeAllCommand(): void
    {
        $allSources = $this->translationLabelSourceRepository->findAll();
        foreach ($allSources as $source) {
            $this->bakeSource($source);
        }
    }

    /**
     * Bake the translations of the specified source back to the csv files
     *
     * @param string $identifier the translation csv that shall be updated
     */
    public function bakeCommand(string $identifier): void
    {
        $source = $this->translationLabelSourceRepository->findOneByIdentifier($identifier);
        if ($source) {
            $this->bakeSource($source);
        }
    }

    /**
     * Reset all overrides
     */
    public function resetAllCommand(bool $yes = false): void
    {
        if (!$yes) {
            $confirmation = $this->output->askConfirmation('Are you sure', false);
            if (!$confirmation) {
                return;
            }
        }

        $allSources = $this->translationLabelSourceRepository->findAll();
        foreach ($allSources as $source) {
            $this->resetSource($source);
        }
    }

    /**
     * Reset all overrides for the specified to the csv file*
     */
    public function resetCommand(string $identifier, bool $yes = false): void
    {
        if (!$yes) {
            $confirmation = $this->output->askConfirmation('Are you sure', false);
            if (!$confirmation) {
                return;
            }
        }

        $source = $this->translationLabelSourceRepository->findOneByIdentifier($identifier);
        if ($source) {
            $this->resetSource($source);
        }
    }

    protected function renderSource(TranslationLabelSource $source): void
    {
        $this->output->outputLine($source->getTitle() . ' : ' . $source->getIdentifier());

        $rows = [];
        foreach ($source->findAllTranslationLabels() as $translationLabel) {
            $row = [$translationLabel->getIdentifier()];
            foreach ($this->locales as $localeIdentifier) {
                $localeChain = $this->localizationService->getLocaleChain(new Locale($localeIdentifier));
                $translation = $translationLabel->findTranslationForLocaleChain($localeChain);
                if ($translation?->getOverride()) {
                    $text = '<info>O::' . $translation->getOverride() . '</info>';
                } elseif ($translation?->getFallback()) {
                    $text = '<comment>F::' . $translation->getFallback() . '</comment>';
                } else {
                    $text = $translation?->__toString() ?? '';
                }
                $row[] = $text;
            }
            $rows[] = $row;
        }

        $this->output->outputTable($rows, array_merge([' '], $this->locales));
        $this->output->outputLine();
    }

    protected function bakeSource(TranslationLabelSource $source): void
    {
        $this->output->outputLine(sprintf('Bake %s : %s', $source->getTitle(), $source->getIdentifier()));

        $overrides = $this->translationOverrideRepository->findBySourceIdentifier($source->getIdentifier());

        // read
        $csvReader = Reader::createFromPath($source->getIdentifier(), 'r');
        $csvReader->setHeaderOffset(0);
        $header = $csvReader->getHeader();
        $records = iterator_to_array($csvReader->getRecords());

        // update records
        /**
         * @var TranslationOverride $override
         */
        foreach ($overrides as $override) {
            // add missing locales to header
            if (!in_array($override->getLocaleIdentifier(), $header)) {
                $header[] = $override->getLocaleIdentifier();
            }
            foreach ($records as $key => $record) {
                if ($record['id'] == $override->getLabelIdentifier()) {
                    $this->output->outputLine(sprintf('- update csv label <info>%s</info> in locale <info>%s</info>', $override->getLabelIdentifier(), $override->getLocaleIdentifier()));
                    $records[$key][$override->getLocaleIdentifier()] = $override->getTranslation();
                }
            }
        }

        $this->output->outputLine();

        // save records
        $csvWriter = Writer::createFromPath($source->getIdentifier(), 'w');
        $csvWriter->insertOne($header);
        foreach ($records as $record) {
            $row = [];
            foreach ($header as $column) {
                $row[$column] = $record[$column] ?? '';
            }
            $csvWriter->insertOne($row);
        }

        // flush caches
        $source->flushCaches();
    }

    /**
     * @param TranslationLabelSource $source
     * @throws \League\Csv\CannotInsertRecord
     * @throws \Sitegeist\CsvPO\Exception\TranslationLabelSourceNotFoundException
     */
    protected function resetSource(TranslationLabelSource $source): void
    {
        $this->output->outputLine(sprintf('Reset %s : %s', $source->getTitle(), $source->getIdentifier()));

        // remove overrides
        $overrides = $this->translationOverrideRepository->findBySourceIdentifier($source->getIdentifier());
        foreach ($overrides as $override) {
            /**
             * @var TranslationOverride $override
             */
            $this->output->outputLine(sprintf('- remove csv label override <info>%s</info> in locale <info>%s</info>', $override->getLabelIdentifier(), $override->getLocaleIdentifier()));
            $this->translationOverrideRepository->remove($override);
        }

        $this->output->outputLine();

        // flush caches
        $source->flushCaches();
    }
}
