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
     * @var string
     */
    protected $description;

    /**
     * @var array<string, string>
     */
    protected $translations;

    /**
     * @var array<string, string>
     */
    protected $overrides;

    /**
     * TranslationLabel constructor.
     * @param string $identifier
     * @param array<string, string> $translations
     * @param array<string, string> $overrides
     */
    public function __construct(string $identifier, string $description, array $translations = [], array $overrides = [])
    {
        $this->identifier = $identifier;
        $this->description = $description;
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param Locale[] $localeChain
     */
    public function findTranslationForLocaleChain(array $localeChain): ?Translation
    {
        // current locale Identifier
        $localeIdentifier = array_keys($localeChain)[0];

        // first check target locale
        $translation = $this->translations[$localeIdentifier] ?? null;
        $override = $this->overrides[$localeIdentifier] ?? null;
        if ($translation || $override) {
            return new Translation($translation, $override);
        }

        // if we use the fallback the override is null
        foreach ($localeChain as $fallbackLocaleIdentifier => $locale) {
            $fallbackTranslation = $this->translations[$fallbackLocaleIdentifier] ?? null;
            $fallbackOverride = $this->overrides[$fallbackLocaleIdentifier] ?? null;
            if ($fallbackTranslation || $fallbackOverride) {
                $fallback = $fallbackOverride ??  $fallbackTranslation;
                return new Translation($translation, $override, $fallback, $fallbackLocaleIdentifier);
            }
        }

        return null;
    }
}
