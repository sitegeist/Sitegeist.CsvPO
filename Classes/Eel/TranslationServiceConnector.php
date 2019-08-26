<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Sitegeist\CsvPO\Service\TranslationService;

class TranslationServiceConnector implements ProtectedContextAwareInterface
{

    /**
     * @var array
     */
    protected $service;

    /**
     * @var TranslationLabelRepository
     * @Flow\InjectConfiguration(path="debugMode")
     */
    protected $debugMode;

    /**
     * Translator constructor.
     * @param string $csvFilename
     * @param string|null $locale
     */
    public function __construct(TranslationService $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $label
     * @param array $arguments
     * @return string
     * @throws \Neos\Flow\I18n\Exception\IndexOutOfBoundsException
     * @throws \Neos\Flow\I18n\Exception\InvalidFormatPlaceholderException
     */
    public function __call(string $label , array $arguments)
    {
        if (strpos($label, 'get') === 0) {
            $label = lcfirst(substr($label, 3));
        }

        if ($this->service->hasTranslation($label)) {
            $translation = $this->service->translate($label, $arguments);
            if (empty($translation) && $this->debugMode) {
                return $this->debugMode ? '-- i18n-translate ' . $label . ' --' : $label;
            }
            return $translation;
        } else {
            return $this->debugMode ? '-- i18n-add ' . $label . ' --' : $label;
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
