<?php

declare(strict_types=1);

namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\Service as LocalizationService;

abstract class AbstractTranslations implements TranslationsInterface
{
    /**
     * @var array<string, Locale[]>
     */
    protected $localizationFallbackChainCache = [];

    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localisationService;

    /**
     * @var FormatResolver
     * @Flow\Inject
     */
    protected $formatResolver;

    /**
     * @var bool
     * @Flow\InjectConfiguration(path="debugMode")
     */
    protected $debugMode;

    /**
     * @param string $translationIdentifier
     * @param array<string|int, mixed> $arguments
     */
    public function __call(string $translationIdentifier, array $arguments = []): string
    {
        if (str_starts_with($translationIdentifier, 'get')) {
            $translationIdentifier = lcfirst(substr($translationIdentifier, 3));
        }
        return $this->getTranslationForIdentifier($translationIdentifier, $arguments);
    }

    public function offsetExists(mixed $offset): bool
    {
        if (is_string($offset)) {
            return $this->getTranslationForIdentifier($offset) ? true : false;
        } else {
            throw new \Exception("only strings are allowed as translation identifiers");
        }
    }

    public function offsetGet(mixed $offset): string
    {
        if (is_string($offset)) {
            return $this->getTranslationForIdentifier($offset);
        } else {
            throw new \Exception("only strings are allowed as translation identifiers");
        }
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \Exception("not implemented");
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \Exception("not implemented");
    }

    /**
     * @return Locale[]
     */
    protected function getCurrentLocalizationFallbackChain(): array
    {
        $localeIdentifier = $this->localisationService->getConfiguration()->getCurrentLocale();
        $localeIdentifierString = (string)$localeIdentifier;

        // determine the fallback chain and cache the result for the current locale
        if (array_key_exists($localeIdentifierString, $this->localizationFallbackChainCache)) {
            $localizationFallbackChain = $this->localizationFallbackChainCache[$localeIdentifierString];
        } else {
            $localizationFallbackChain = $this->localisationService->getLocaleChain($localeIdentifier);
            $this->localizationFallbackChainCache[$localeIdentifierString] = $localizationFallbackChain;
        }
        return $localizationFallbackChain;
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
