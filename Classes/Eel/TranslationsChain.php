<?php

declare(strict_types=1);

namespace Sitegeist\CsvPO\Eel;

use Sitegeist\CsvPO\Domain\TranslationLabelSource;

class TranslationsChain extends AbstractTranslations
{
    /**
     * @var TranslationLabelSource[]
     */
    protected $translationLabelSources;
    public function __construct(TranslationLabelSource ...$translationLabelSources)
    {
        $this->translationLabelSources = array_reverse($translationLabelSources);
    }

    public function getTranslationForIdentifier(string $translationIdentifier, array $arguments = []): string
    {
        $localizationFallbackChain = $this->getCurrentLocalizationFallbackChain();

        foreach ($this->translationLabelSources as $translationLabelSource) {
            if ($translationLabel = $translationLabelSource->findTranslationLabelByIdentifier($translationIdentifier)) {
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
            }
        }
        return $this->debugMode ? '-- i18n-add ' . $translationIdentifier . ' --' : $translationIdentifier;
    }

    public function jsonSerialize(): mixed
    {
        $result = [];
        $localizationFallbackChain = $this->getCurrentLocalizationFallbackChain();
        $translationLabelSourcesInReverseOrder = array_reverse($this->translationLabelSources);
        foreach ($translationLabelSourcesInReverseOrder as $translationSource) {
            foreach ($translationSource->findAllTranslationLabels() as $translationLabel) {
                $translation = $translationLabel->findTranslationForLocaleChain($localizationFallbackChain);
                $result[$translationLabel->getIdentifier()] = (string)$translation;
            }
        }
        return $result;
    }
}
