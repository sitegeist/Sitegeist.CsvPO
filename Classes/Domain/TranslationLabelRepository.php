<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Frontend\VariableFrontend;
use Sitegeist\CsvPO\Domain\TranslationOverride;
use Sitegeist\CsvPO\Domain\TranslationOverrideRepository;
use League\Csv\Reader;

class TranslationLabelRepository
{
    /**
     * @var string
     */
    protected $Persistence_Object_Identifier;

    /**
     * @var string
     */
    protected $csvFilename;

    /**
     * @var TranslationLabel[]
     */
    protected $translations = [];

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $translationCache;

    /**
     * @var TranslationOverrideRepository
     * @Flow\Inject
     */
    protected $translationOverrideRepository;

    public function __construct(string $csvFilename)
    {
        $this->csvFilename = $csvFilename;
        $this->Persistence_Object_Identifier = $csvFilename;
    }

    protected function initializeObject() {

        // read data from csv and overrides
        $cacheIdentifier = md5($this->csvFilename);
        if ($this->translationCache->has($cacheIdentifier)) {
            $translationData = $this->translationCache->get($cacheIdentifier);
        } else {
            $translationData = [
                'translations' => $this->readCsvData($this->csvFilename),
                'overrides' => $this->readOverrideData($this->csvFilename)
            ];
            $this->translationCache->set($cacheIdentifier, $translationData, [$cacheIdentifier]);
        }

        // instantiate the translation objects
        foreach ($translationData['translations'] as $labelIdentifier => $translation) {
            $this->translations[$labelIdentifier] = new TranslationLabel($labelIdentifier, $translation, $translationData['overrides'][$labelIdentifier] ?? []);
        }
    }

    public function findOneByIdentifier(string $identifier)
    {
        if (array_key_exists($identifier, $this->translations)) {
            return $this->translations[$identifier];
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getCsvFilename(): string
    {
        return $this->csvFilename;
    }

    public function findAll()
    {
        return $this->translations;
    }

    protected function readCsvData ($csvFilename): array
    {
        $translations = [];
        $csv = Reader::createFromPath($csvFilename, 'r');
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();

        foreach ($csv->getRecords() as $csvRecord) {
            $identifier = $csvRecord[ $header[0] ];
            unset($csvRecord[$header[0]]);
            $translations[$identifier] = $csvRecord;
        }
        return $translations;
    }

    protected function readOverrideData ($csvFilename): array
    {
        $overrides = [];
        $queryResult = $this->translationOverrideRepository->findBySourceIdentifier($this->csvFilename);
        foreach ($queryResult as $translationLabel) {
            /**
             * @var TranslationOverride $translationLabel
             */
            if (!array_key_exists($translationLabel->getTranslationIdentifier(), $overrides)) {
                $overrides[$translationLabel->getTranslationIdentifier()] = [];
            }
            $overrides[$translationLabel->getTranslationIdentifier()][$translationLabel->getLocaleIdentifier()] = $translationLabel->getTranslation();
        }
        return $overrides;
    }
}
