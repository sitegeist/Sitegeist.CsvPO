<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Frontend\VariableFrontend;
use Sitegeist\CsvPO\Domain\TranslationOverride;
use Sitegeist\CsvPO\Domain\TranslationOverrideRepository;
use League\Csv\Reader;

class TranslationLabelSource
{
    /**
     * @var string
     */
    protected $identifier;

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
     * @var bool
     * @Flow\InjectConfiguration(path="management.enabled")
     */
    protected $managementEnabled;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="management.fileExtension")
     */
    protected $fileExtension;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="management.resourcePath")
     */
    protected $resourcePath;

    /**
     * @var TranslationOverrideRepository
     * @Flow\Inject
     */
    protected $translationOverrideRepository;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        $this->Persistence_Object_Identifier = $identifier;
    }

    protected function initializeObject() {

        // read data from csv and overrides
        $cacheIdentifier = md5($this->identifier);
        if ($this->translationCache->has($cacheIdentifier)) {
            $translationData = $this->translationCache->get($cacheIdentifier);
        } else {
            $translationData = [
                'translations' => $this->readCsvData($this->identifier),
                'overrides' => $this->managementEnabled ? $this->readOverrideData($this->identifier) : []
            ];
            $this->translationCache->set($cacheIdentifier, $translationData);
        }

        // instantiate the translation objects
        foreach (array_keys($translationData['translations']) as $labelIdentifier) {
            $this->translations[$labelIdentifier] = new TranslationLabel(
                $labelIdentifier,
                $translationData['translations'][$labelIdentifier]['description'] ?? '',
                $translationData['translations'][$labelIdentifier] ?? [],
                $translationData['overrides'][$labelIdentifier] ?? []
            );
        }
    }

    /**
     * @param string $identifier
     * @return TranslationLabel|null
     */
    public function findTranslationLabelByIdentifier(string $identifier): ?TranslationLabel
    {
        if (array_key_exists($identifier, $this->translations)) {
            return $this->translations[$identifier];
        } else {
            return null;
        }
    }

    /**
     * @return TranslationLabel[]
     */
    public function findAllTranslationLabels(): array
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        $title = $this->identifier;
        $title = str_replace("resource://", '', $title);
        $title = str_replace( '/' . $this->resourcePath . '/', ' - ', $title);
        $title = str_replace($this->fileExtension, '', $title);
        return $title;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return basename($this->identifier);
    }

    /**
     * @return string
     */
    public function getPackageKey(): string
    {
        $path = str_replace("resource://", '', $this->identifier);
        $parts = explode('/', $path);
        return (string) array_shift($parts);
    }

    /**
     * @return string
     */
    public function getResourcePath(): string
    {
        $path = str_replace("resource://", '', $this->identifier);
        $parts = explode('/', $path);
        array_shift($parts);
        return implode("/", $parts);
    }

    /**
     * @return array
     */
    protected function readCsvData (): array
    {
        $translations = [];
        $csv = Reader::createFromPath($this->identifier, 'r');
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();

        foreach ($csv->getRecords() as $csvRecord) {
            $identifier = $csvRecord[ $header[0] ];
            unset($csvRecord[$header[0]]);
            $translations[$identifier] = $csvRecord;
        }
        return $translations;
    }

    /**
     * @return array
     */
    protected function readOverrideData (): array
    {
        $overrides = [];
        $queryResult = $this->translationOverrideRepository->findBySourceIdentifier($this->identifier);
        foreach ($queryResult as $translationLabel) {
            /**
             * @var TranslationOverride $translationLabel
             */
            if (!array_key_exists($translationLabel->getLabelIdentifier(), $overrides)) {
                $overrides[$translationLabel->getLabelIdentifier()] = [];
            }
            $overrides[$translationLabel->getLabelIdentifier()][$translationLabel->getLocaleIdentifier()] = $translationLabel->getTranslation();
        }
        return $overrides;
    }

    /**
     * Flush the caches of this translation source
     */
    public function flushCaches (): void
    {
        $cacheIdentifier = md5($this->identifier);
        if  ($this->translationCache->has($cacheIdentifier)) {
            $this->translationCache->remove($cacheIdentifier);
        }
    }
}
