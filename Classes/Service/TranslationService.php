<?php
namespace Sitegeist\CsvPO\Service;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Service as LocalizationService;
use Neos\Flow\I18n\Locale;
use Sitegeist\CsvPO\Domain\Model\TranslationLabel;
use Sitegeist\CsvPO\Domain\Repository\TranslationLabelRepository;
use Neos\Utility\Arrays;

class TranslationService
{
    /**
     * @var string
     */
    protected $csvFilename;

    /**
     * @var string
     */
    protected $localeIdentifier;

    /**
     * @var array
     */
    protected $localeIdentifierChain;

    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localizationService;

    /**
     * @var FormatResolver
     * @Flow\Inject
     */
    protected $formatResolver;

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $translationCache;

    /**
     * @var TranslationLabelRepository
     * @Flow\Inject
     */
    protected $translationLabelRepository;

    /**
     * @var array
     */
    protected $translations = [];

    /**
     * Translator constructor.
     * @param string $csvFilename
     * @param string|null $locale
     */
    public function __construct(string $csvFilename, string $localeIdentifier = null)
    {
        $this->csvFilename = $csvFilename;
        $this->localeIdentifier = $localeIdentifier;
    }

    /**
     * @throws \Neos\Cache\Exception
     */
    public function initializeObject()
    {
        $locale = $this->localeIdentifier ? new Locale($this->localeIdentifier) : $this->localizationService->getConfiguration()->getCurrentLocale();

        $this->localeIdentifierChain = [];
        foreach ($this->localizationService->getLocaleChain($locale) as $localeInChain) {
            $this->localeIdentifierChain[] = $localeInChain->__toString();
        }

        $cacheIdentifier = md5($this->csvFilename);
        if ($this->translationCache->has($cacheIdentifier)) {
            $this->translations = $this->translationCache->get($cacheIdentifier);
        } else {
            $translations = $this->readTranslations($this->csvFilename);
            $overrides = $this->readOverrides($this->csvFilename);
            $this->translations = Arrays::arrayMergeRecursiveOverrule($translations, $overrides);
            $this->translationCache->set($cacheIdentifier, $this->translations, [$cacheIdentifier]);
        }
    }

    /**
     * @param string $csvFilename
     * @return array
     */
    public function readTranslations(string $csvFilename)
    {
        $translations = [];
        if (($csvFileHandle = fopen($csvFilename, "r")) !== false) {
            $header = array_map(function($item){return trim($item);}, fgetcsv($csvFileHandle));
            while (($row = fgetcsv($csvFileHandle)) !== false) {
                $id = $row[0];
                $translation = [];
                foreach ($header as $index => $locale) {
                    if ($index > 0 && array_key_exists($index, $row) && !empty($row[$index])) {
                        $translation[$locale] = trim($row[$index]);
                    }
                }
                $translations[$id] = $translation;
            }
            fclose($csvFileHandle);
        }
        return $translations;
    }

    /**
     * @param string $csvFilename
     * @return array
     */
    public function readOverrides(string $csvFilename)
    {
        $overrides = [];
        $queryResult = $this->translationLabelRepository->findBySource($csvFilename);
        foreach ($queryResult as $translationLabel) {
            /**
             * @var TranslationLabel $translationLabel
             */
            if (!array_key_exists($translationLabel->getLabel(), $overrides)) {
                $overrides[$translationLabel->getLabel()] = [];
            }
            $overrides[$translationLabel->getLabel()][$translationLabel->getLocale()] = $translationLabel->getTranslation();
        }
        return $overrides;
    }

    /**
     * @return string[]
     */
    public function getAllLabels(): array
    {
        return array_keys($this->translations);
    }

    /**
     * @param string $label
     * @return bool
     */
    public function hasTranslation(string $label): bool
    {
        return array_key_exists($label, $this->translations);
    }

    /**
     * @param string $label
     * @param array $arguments
     * @return string
     * @throws \Neos\Flow\I18n\Exception\IndexOutOfBoundsException
     * @throws \Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException
     */
    public function translate(string $label, array $arguments = []): string
    {
        $translation = $label;
        foreach ($this->localeIdentifierChain as $localeIdentifier) {
            if (array_key_exists($localeIdentifier, $this->translations[$label]) && !empty($this->translations[$label][$localeIdentifier])) {
                $translation = $this->translations[$label][$localeIdentifier];
                break;
            }
        }
        if (count($arguments) > 0) {
            return $this->formatResolver->resolvePlaceholders($translation, $arguments[0]);
        } else {
            return $translation;
        }
    }

    /**
     *
     */
    public function getAll ()
    {
        return array_keys($this->translations);
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }


}
