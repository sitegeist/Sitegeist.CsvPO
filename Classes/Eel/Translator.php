<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Service as LocalizationService;
use Neos\Flow\I18n\Locale;

class Translator implements ProtectedContextAwareInterface
{
    /**
     * @var string
     */
    protected $csvFilename;

    /**
     * @var FormatResolver
     * @Flow\Inject
     */
    protected $formatResolver;

    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localizationService;

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $translationCache;

    /**
     * @var array
     */
    protected $localeIdentifierChain;

    /**
     * Translator constructor.
     * @param string $csvFilename
     */
    public function __construct(string $csvFilename)
    {
        $this->csvFilename = $csvFilename;
    }

    /**
     * @throws \Neos\Cache\Exception
     */
    public function initializeObject()
    {
        $this->localeIdentifierChain = [];
        $locale = $this->localizationService->getConfiguration()->getCurrentLocale();
        foreach ($this->localizationService->getLocaleChain($locale) as $localeInChain) {
            $this->localeIdentifierChain[] = $localeInChain->__toString();
        }

        $cacheIdentifier = md5($this->csvFilename);
        if ($this->translationCache->has($cacheIdentifier)) {
            $this->translations = $this->translationCache->get($cacheIdentifier);
        } else {
            $this->translations = $this->readTranslations($this->csvFilename);
            $this->translationCache->set($cacheIdentifier, $this->translations);
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
     * @param string $name
     * @param array $arguments
     * @return string
     * @throws \Neos\Flow\I18n\Exception\IndexOutOfBoundsException
     * @throws \Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException
     */
    public function __call(string $name , array $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $name = lcfirst(substr($name, 3));
        }

        if (array_key_exists($name, $this->translations)) {
            $translation = '-- i18n(translate-' . $name . ') --';
            foreach ($this->localeIdentifierChain as $localeIdentifier) {
                if (array_key_exists($localeIdentifier, $this->translations[$name]) && !empty($this->translations[$name][$localeIdentifier])) {
                    $translation = $this->translations[$name][$localeIdentifier];
                    break;
                }
            }
            if (count($arguments) > 0) {
                return $this->formatResolver->resolvePlaceholders($translation, $arguments[0]);
            } else {
                return $translation;
            }
        } else {
            return '-- i18n(add-' . $name . ') --';
        }
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
