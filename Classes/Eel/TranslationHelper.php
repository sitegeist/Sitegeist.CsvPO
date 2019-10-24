<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Sitegeist\CsvPO\Domain\TranslationLabelSourceRepository;

class TranslationHelper implements ProtectedContextAwareInterface
{
    /**
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationSourceRepository;

    /**
     * @param string $csvFile
     */
    public function create(string $csvFile)
    {
        $translationSource = $this->translationSourceRepository->findOneByIdentifier($csvFile);
        return new TranslationSourceConnector($translationSource);
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
