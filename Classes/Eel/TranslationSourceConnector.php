<?php

declare(strict_types=1);

namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Service as LocalizationService;
use Sitegeist\CsvPO\Domain\TranslationLabelSource;

class TranslationSourceConnector implements TranslationsInterface
{
    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localisationService;

    /**
     * @var TranslationLabelSource
     */
    protected $translationSource;

    /**
     * @var FormatResolver
     * @Flow\Inject
     */
    protected $formatResolver;

    /**
     * @var array<string, Locale[]>
     */
    protected $localizationFallbackChainCache = [];

    /**
     * @var bool
     * @Flow\InjectConfiguration(path="debugMode")
     */
    protected $debugMode;

    /**
     * TranslationSourceConnector constructor.
     * @param TranslationLabelSource $translationSource
     */
    public function __construct(TranslationLabelSource $translationSource)
    {
        $this->translationSource = $translationSource;
    }

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function __call(string $translationIdentifier, array $arguments = []): string
    {
        if (strpos($translationIdentifier, 'get') === 0) {
            $translationIdentifier = lcfirst(substr($translationIdentifier, 3));
        }
        return $this->getTranslationForIdentifier($translationIdentifier, $arguments);
    }

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function getTranslationForIdentifier(string $translationIdentifier, array $arguments = []): string
    {
        $localizationFallbackChain = $this->getCurrentLocalizationFallbackChain();

        if ($translationLabel = $this->translationSource->findTranslationLabelByIdentifier($translationIdentifier)) {
            $translation = $translationLabel->findTranslationForLocaleChain($localizationFallbackChain);
            if (isset($arguments[0]) && is_array($arguments[0])) {
                $translationResult = $this->formatResolver->resolvePlaceholders($translation->__toString(), $arguments[0]);
            } else {
                $translationResult = $translation->__toString();
            }
            if (!empty($translationResult)) {
                return $translationResult;
            } else {
                return $this->debugMode ? '-- i18n-translate ' . $translationIdentifier . ' --' : $translationIdentifier;
            }
        } else {
            return $this->debugMode ? '-- i18n-add ' . $translationIdentifier . ' --' : $translationIdentifier;
        }
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
     * @return array<string,string>
     */
    public function jsonSerialize(): array
    {
        $result = [];
        $localizationFallbackChain = $this->getCurrentLocalizationFallbackChain();
        foreach ($this->translationSource->findAllTranslationLabels() as $translationLabel) {
            $translation = $translationLabel->findTranslationForLocaleChain($localizationFallbackChain);
            $result[$translationLabel->getIdentifier()] = (string) $translation;
        }
        return $result;
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
