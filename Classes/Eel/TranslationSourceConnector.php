<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Service as LocalizationService;
use Sitegeist\CsvPO\Domain\TranslationLabelSource;
use Sitegeist\CsvPO\Service\TranslationService;

class TranslationSourceConnector implements ProtectedContextAwareInterface, \JsonSerializable
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
     * @var Locale[]
     */
    protected $localizationFallbackChainCache = [];

    /**
     * @var TranslationLabelSource
     * @Flow\InjectConfiguration(path="debugMode")
     */
    protected $debugMode;

    /**
     * TranslationSourceConnector constructor.
     * @param TranslationLabelSource $translationSource
     * @param string $localeIdentifier
     * @param array $localizationFallbackChain
     */
    public function __construct(TranslationLabelSource $translationSource)
    {
        $this->translationSource = $translationSource;
    }

    /**
     * @param string $translationIdentifier
     * @param array $arguments
     * @return string
     * @throws \Neos\Flow\I18n\Exception\IndexOutOfBoundsException
     * @throws \Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException
     */
    public function __call(string $translationIdentifier , array $arguments)
    {
        $localeIdentifier = $this->localisationService->getConfiguration()->getCurrentLocale();
        $localeIdentifierString = (string)$localeIdentifier;

        // determine the fallback chain and cache the result for the current locale
        if (array_key_exists($localeIdentifierString, $this->localizationFallbackChainCache)) {
            $localizationFallbackChain = $this->localizationFallbackChainCach[$localeIdentifierString];
        } else {
            $localizationFallbackChain = $this->localisationService->getLocaleChain($localeIdentifier);
            $this->localizationFallbackChainCach[$localeIdentifierString] = $localizationFallbackChain;
        }

        if (strpos($translationIdentifier, 'get') === 0) {
            $translationIdentifier = lcfirst(substr($translationIdentifier, 3));
        }

        if ($translationLabel = $this->translationSource->findTranslationLabelByIdentifier($translationIdentifier)) {
            $translation = $translationLabel->findTranslationForLocaleChain($localizationFallbackChain);
            if (isset($arguments[0]) && is_array($arguments[0])) {
                $translationResult = $this->formatResolver->resolvePlaceholders($translation->__toString(), $arguments[0]);
            } else {
                $translationResult = $translation->__toString();
            }
            if (empty($translationResult) && $this->debugMode) {
                return $this->debugMode ? '-- i18n-translate ' . $translationIdentifier . ' --' : $translationIdentifier;
            }
            return $translationResult;
        } else {
            return $this->debugMode ? '-- i18n-add ' . $translationIdentifier . ' --' : $translationIdentifier;
        }
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $result = [];

        foreach ($this->translationSource->findAllTranslationLabels() as $translationLabel) {
            $translation = $translationLabel->findTranslationForLocaleChain($this->localizationFallbackChain);
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
