<?php
namespace Sitegeist\CsvPO\Command;

use League\Csv\Reader;
use League\Csv\Writer;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\CsvPO\Domain\TranslationOverrideRepository;
use Sitegeist\CsvPO\Domain\TranslationOverride;
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
     * @var array
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
    public function listCommand() {
        $allSources = $this->translationLabelSourceRepository->findAll();
        $rows = [];
        foreach ($allSources as $source) {
            $rows[] = [$source->getTitle(), $source->getIdentifier()];
        }
        $this->output->outputTable($rows, ['Title', 'Identifier']);
    }

    /**
     * Show the translations of the specified source
     * @param $identifier
     */
    public function showCommand(string $identifier) {
        $source = $this->translationLabelSourceRepository->findOneByIdentifier($identifier);
        $rows = [];
        foreach ($source->findAllTranslationLabels() as $translationLabel) {
            $row = [$translationLabel->getIdentifier()];
            foreach ($this->locales as $localeIdentifier) {
                $localeChain = $this->localizationService->getLocaleChain( new Locale($localeIdentifier) );
                $translation = $translationLabel->findTranslationForLocaleChain($localeChain);
                if ($translation->getOverride()) {
                    $text = '<info>' . $translation->getOverride() . '</info>';
                } else if ($translation->getFallback()) {
                    $text = '<comment>' . $translation->getFallback() . '</comment>';
                } else {
                    $text = $translation->__toString();
                }
                $row[] =$text ;
            }
            $rows[] = $row;
        }
        $this->output->outputTable($rows, array_merge([' '], $this->locales));
    }

    /**
     * Bake the translations of the specified source back to the csv files
     *
     * @param string $identifier the translation csv that shall be updated
     * @param bool $deleteOverrides Delete override records after updating
     */
    public function bakeCommand(string $identifier, bool $deleteOverrides = false) {
        $overrides = $this->translationOverrideRepository->findBySourceIdentifier($identifier);

        // read
        $csvReader = Reader::createFromPath($identifier, 'r');
        $csvReader->setHeaderOffset(0);
        $header = $csvReader->getHeader();
        $records = iterator_to_array($csvReader->getRecords());

        // update
        /**
         * @var $override TranslationOverride
         */
        foreach ($overrides as $override) {
            foreach ($records as $key => $record) {
                if ($record['id'] == $override->getLabelIdentifier()) {
                    $this->output->outputLine(sprintf('Update label <info>%s</info> from <info>"%s"</info> to <info>"%s"</info>',  $record['id'], $records[$key][$override->getLocaleIdentifier()] ?? '', $override->getTranslation()));
                    $records[$key][$override->getLocaleIdentifier()] = $override->getTranslation();
                }
                if ($deleteOverrides) {
                    $this->translationOverrideRepository->remove($override);
                }
            }
        }

        // save
        $csvWriter = Writer::createFromPath($identifier, 'w');
        $csvWriter->insertOne($header);
        $csvWriter->insertAll($records);

        // flush caches
        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($identifier);
        $translationLabelSource->flushCaches();
    }
}
