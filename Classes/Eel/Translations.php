<?php

declare(strict_types=1);

namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Sitegeist\CsvPO\Domain\TranslationLabelSource;

class Translations extends AbstractTranslations
{
    protected TranslationLabelSource $translationSource;

    /**
     * TranslationSourceConnector constructor.
     * @param TranslationLabelSource $translationSource
     */
    public function __construct(
        TranslationLabelSource $translationSource
    ) {
        $this->translationSource = $translationSource;
    }

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function getTranslationForIdentifier(string $translationIdentifier, array $arguments = []): string
    {
        $localizationFallbackChain = $this->getCurrentLocalizationFallbackChain();
        if ($translationLabel = $this->translationSource->findTranslationLabelByIdentifier($translationIdentifier)) {
            $translation = $translationLabel->findTranslationForLocaleChain($localizationFallbackChain);
            if ($translation) {
                if (isset($arguments[0]) && is_array($arguments[0])) {
                    return $this->formatResolver->resolvePlaceholders($translation->__toString(), $arguments[0]);
                } elseif (!empty($arguments)) {
                    return $this->formatResolver->resolvePlaceholders($translation->__toString(), $arguments);
                } else {
                    return $translation->__toString();
                }
            }
            return $this->debugMode ? '-- i18n-translate ' . $translationIdentifier . ' --' : $translationIdentifier;
        } else {
            return $this->debugMode ? '-- i18n-add ' . $translationIdentifier . ' --' : $translationIdentifier;
        }
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
}
