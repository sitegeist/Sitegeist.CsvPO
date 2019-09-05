<?php
namespace Sitegeist\CsvPO\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\CsvPO\Domain\TranslationOverrideRepository;
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
                $translation = $translationLabel->getTranslation($localeIdentifier, $localeChain);
                $row[] = $translation->translate();
            }
            $rows[] = $row;
        }
        $this->output->outputTable($rows, array_merge([' '], $this->locales));
    }

}
