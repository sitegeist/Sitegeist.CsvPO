<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\FormatResolver;
use Sitegeist\CsvPO\Domain\TranslationLabelSource;
use Sitegeist\CsvPO\Service\TranslationService;

class TranslationSourceConnector implements ProtectedContextAwareInterface
{

    /**
     * @var TranslationLabelSource
     */
    protected $translationSource;

    /**
     * @var string
     */
    protected $localeIdentifier;

    /**
     * @var FormatResolver
     * @Flow\Inject
     */
    protected $formatResolver;

    /**
     * @var Locale[]
     */
    protected $localizationFallbackChain = [];

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
    public function __construct(TranslationLabelSource $translationSource, string $localeIdentifier, array $localizationFallbackChain = [])
    {
        $this->translationSource = $translationSource;
        $this->localeIdentifier = $localeIdentifier;
        $this->localizationFallbackChain = $localizationFallbackChain;
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
        if (strpos($translationIdentifier, 'get') === 0) {
            $translationIdentifier = lcfirst(substr($translationIdentifier, 3));
        }

        if ($translationLabel = $this->translationSource->findTranslationLabelByIdentifier($translationIdentifier)) {
            $translation = $translationLabel->findTranslationForLocaleChain($this->localizationFallbackChain);
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
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }


}
