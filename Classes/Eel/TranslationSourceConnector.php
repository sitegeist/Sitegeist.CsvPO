<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\I18n\Locale;
use Sitegeist\CsvPO\Domain\TranslationLabelRepository;
use Sitegeist\CsvPO\Service\TranslationService;

class TranslationSourceConnector implements ProtectedContextAwareInterface
{

    /**
     * @var TranslationLabelRepository
     */
    protected $translationSource;

    /**
     * @var Locale[]
     */
    protected $localizationFallbackChain = [];

    /**
     * @var TranslationLabelRepository
     * @Flow\InjectConfiguration(path="debugMode")
     */
    protected $debugMode;

    /**
     * Translator constructor.
     * @param string $csvFilename
     * @param Locale $locale
     * @param Locale[] $localizationFallbacklChain
     * @param string|null $locale
     */
    public function __construct(TranslationLabelRepository $translationSource, array $localizationFallbackChain = [])
    {
        $this->translationSource = $translationSource;
        $this->localizationFallbackChain = $localizationFallbackChain;
    }

    /**
     * @param string $identifier
     * @param array $arguments
     * @return string
     * @throws \Neos\Flow\I18n\Exception\IndexOutOfBoundsException
     * @throws \Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException
     */
    public function __call(string $identifier , array $arguments)
    {
        if (strpos($identifier, 'get') === 0) {
            $identifier = lcfirst(substr($identifier, 3));
        }

        if ($translationLabel = $this->translationSource->findOneByIdentifier($identifier)) {
            $translationResult = $translationLabel->translate($arguments, $this->localizationFallbackChain);
            if (empty($translationResult) && $this->debugMode) {
                return $this->debugMode ? '-- i18n-translate ' . $identifier . ' --' : $identifier;
            }
            return $translationResult;
        } else {
            return $this->debugMode ? '-- i18n-add ' . $identifier . ' --' : $identifier;
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
