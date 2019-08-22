<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Eel\ProtectedContextAwareInterface;

class TranslationHelper implements ProtectedContextAwareInterface
{

    /**
     * @param string $csvFile
     */
    public function create(string $csvFile)
    {
        return new Translator($csvFile);
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
