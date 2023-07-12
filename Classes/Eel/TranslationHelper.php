<?php

namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Sitegeist\CsvPO\Domain\TranslationLabelSource;
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
        $translationSource = $this->translationSourceRepository->findOneByIdentifier($csvFile);
        if ($translationSource) {
            return new TranslationSourceConnector($translationSource);
        } else {
            throw new TranslationLabelSourceNotFoundException(sprintf('Translation source %s was not found', $csvFile));
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
