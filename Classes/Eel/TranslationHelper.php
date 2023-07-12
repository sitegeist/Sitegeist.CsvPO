<?php

namespace Sitegeist\CsvPO\Eel;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Sitegeist\CsvPO\Domain\TranslationLabelSourceRepository;
use Sitegeist\CsvPO\Exception\TranslationLabelSourceNotFoundException;

class TranslationHelper implements ProtectedContextAwareInterface
{
    /**
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationSourceRepository;

    public function create(string|null ...$csvFiles): TranslationsInterface
    {
        $csvFiles = array_filter($csvFiles);
        if (count($csvFiles) === 0) {
            throw new TranslationLabelSourceNotFoundException('no translation source was not given');
        } elseif (count($csvFiles) === 1) {
            $translationSource = $this->translationSourceRepository->findOneByIdentifier($csvFiles[0]);
            if ($translationSource) {
                return new Translations($translationSource);
            }
            throw new TranslationLabelSourceNotFoundException(sprintf('Translation source %s was not found', $csvFiles[0]));
        }

        $translationSources = [];
        foreach ($csvFiles as $csvFile) {
            $translationSource = $this->translationSourceRepository->findOneByIdentifier($csvFile);
            if ($translationSource) {
                $translationSources[] = $translationSource;
            } else {
                throw new TranslationLabelSourceNotFoundException(sprintf('Translation source %s was not found', $csvFile));
            }
        }
        return new TranslationsChain(...$translationSources);
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
