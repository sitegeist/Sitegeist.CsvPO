<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Eel\ProtectedContextAwareInterface;
use Sitegeist\CsvPO\Service\TranslationService;

class TranslationHelper implements ProtectedContextAwareInterface
{

    /**
     * @param string $csvFile
     */
    public function create(string $csvFile, string $localeIdentifier = null)
    {
        $service = new TranslationService($csvFile, $localeIdentifier);
        return new TranslationServiceConnector($service);
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return $methodName == 'create';
    }

}
