<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Service as LocalizationService;
use Neos\Flow\I18n\Locale;

class Translator implements ProtectedContextAwareInterface
{
    protected $header;
    protected $translations;

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
     * @var array
     */
    protected $localeIdentifierChain;

    public function __construct($csvFile)
    {
        $this->translations = [];
        if (($csvFileHandle = fopen($csvFile, "r")) !== false) {
            $header = array_map(function($item){return trim($item);}, fgetcsv($csvFileHandle));
            while (($row = fgetcsv($csvFileHandle)) !== false) {
                $id = $row[0];
                $translation = [];
                foreach ($header as $index => $locale) {
                    if ($index > 0 && array_key_exists($index, $row) && !empty($row[$index])) {
                        $translation[$locale] = trim($row[$index]);
                    }
                }
                $this->translations[$id] = $translation;
            }
            fclose($csvFileHandle);
        }
    }

    public function initializeObject()
    {
        $this->localeIdentifierChain = [];
        $locale = $this->localizationService->getConfiguration()->getCurrentLocale();
        foreach ($this->localizationService->getLocaleChain($locale) as $localeInChain) {
            $this->localeIdentifierChain[] = $localeInChain->__toString();
        }
    }

    public function __call(string $name , array $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $name = lcfirst(substr($name, 3));
        }

        if (array_key_exists($name, $this->translations)) {
            $translation = 'i18n(translate-' . $name . ')';
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
            return 'i18n(add-' . $name . ')';
        }
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
