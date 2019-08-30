<?php
namespace Sitegeist\CsvPO\Eel;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Sitegeist\CsvPO\Domain\TranslationLabelSourceRepository;
use \Neos\Flow\I18n\Service as LocalizationService;

class TranslationHelper implements ProtectedContextAwareInterface
{
    /**
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationSourceRepository;

    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localisationService;

    /**
     * @param string $csvFile
     */
    public function create(string $csvFile)
    {
        $translationSource = $this->translationSourceRepository->findOneByIdentifier($csvFile);

        $currentLocale = $this->localisationService->getConfiguration()->getCurrentLocale();
        $localeChain = $this->localisationService->getLocaleChain($currentLocale);

        return new TranslationSourceConnector($translationSource, $localeChain);
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
