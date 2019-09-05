<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Locale;

class TranslationLabel
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $translations;

    /**
     * @var array
     */
    protected $overrides;

    /**
     * TranslationLabel constructor.
     * @param string $identifier
     * @param array $translations
     * @param array $overrides
     */
    public function __construct(string $identifier, array $translations = [], array $overrides = [])
    {
        $this->identifier = $identifier;
        $this->translations = $translations;
        $this->overrides = $overrides;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }


    /**
     * @param string $localeIdentifier
     * @param array|null $localeChain
     * @return Translation
     */
    public function getTranslation(string $localeIdentifier, array $localeChain = null): Translation
    {
        // first check target locale
        $translation = $this->translations[$localeIdentifier] ?? null;
        $override = $this->overrides[$localeIdentifier] ?? null;
        if ($translation || $override) {
            return new Translation($localeIdentifier, $translation, $override);
        }

        // if we use the fallback the override is null
        foreach ($localeChain as $chainLocaleIdentifier => $locale) {
            $translation = $this->translations[$chainLocaleIdentifier] ?? null;
            $override = $this->overrides[$chainLocaleIdentifier] ?? null;
            if ($translation || $override) {
                return new Translation($chainLocaleIdentifier, $override ?? $translation, null);
            }
        }

        // final fallback
        $localeIdentifier = array_keys($localeChain)[0];
        return new Translation($localeIdentifier, '', null);
    }
}
