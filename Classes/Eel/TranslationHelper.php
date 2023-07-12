<?php

namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Sitegeist\CsvPO\Domain\TranslationLabelSourceRepository;
use Sitegeist\CsvPO\Exception\TranslationLabelSourceNotFoundException;

class TranslationHelper implements ProtectedContextAwareInterface
{
    /**
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationSourceRepository;

    /**
     * @param string $csvFile
     * @return TranslationSourceConnector
     */
    public function create(string $csvFile): TranslationSourceConnector
    {
        try {
            $translationSource = $this->translationSourceRepository->findOneByIdentifier($csvFile);
            return new TranslationSourceConnector($translationSource);
        } catch (TranslationLabelSourceNotFoundException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
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
