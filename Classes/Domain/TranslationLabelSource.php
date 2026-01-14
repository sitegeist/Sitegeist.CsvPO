<?php

namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Cache\CacheManager;
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
     * @var string
     * @Flow\InjectConfiguration(path="management.fileExtension")
     */
    protected $fileExtension;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="management.resourcePath")
     */
    protected $resourcePath;

    /**
     * @var TranslationOverrideRepository
     * @Flow\Inject
     */
    protected $translationOverrideRepository;

    /**
     * @var CacheManager
     * @Flow\Inject
     */
    protected $cacheManager;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    protected function initializeObject(): void
    {

        // read data from csv and overrides
        $cacheIdentifier = md5($this->identifier);
        if ($this->translationCache->has($cacheIdentifier)) {
            $translationData = $this->translationCache->get($cacheIdentifier);
        } else {
            $translationData = [
                'translations' => $this->readCsvData(),
                'overrides' => $this->managementEnabled ? $this->readOverrideData() : []
            ];
            $this->translationCache->set($cacheIdentifier, $translationData);
        }

        // instantiate the translation objects
        foreach (array_keys($translationData['translations']) as $labelIdentifier) {
            $labelIdentifier = (string) $labelIdentifier;
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
        $title = str_replace('/' . $this->resourcePath . '/', ' - ', $title);
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
     * @return array<string, array<string, string|null>>
     */
    protected function readCsvData(): array
    {
        $translations = [];
        $csv = Reader::createFromPath($this->identifier, 'r');
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();

        /**
         * @var \Iterator<array<string|null>> $csvRecords
         */
        $csvRecords = $csv->getRecords();
        foreach ($csvRecords as $csvRecord) {
            $identifier = $csvRecord[ $header[0] ];
            unset($csvRecord[$header[0]]);
            $translations[$identifier] = $csvRecord;
        }
        return $translations;
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function readOverrideData(): array
    {
        $overrides = [];
        $queryResult = $this->translationOverrideRepository->findBySourceIdentifier($this->identifier);
        foreach ($queryResult as $translationOverride) {
            /**
             * @var TranslationOverride $translationOverride
             */
            if (!array_key_exists($translationOverride->getLabelIdentifier(), $overrides)) {
                $overrides[$translationOverride->getLabelIdentifier()] = [];
            }
            $overrides[$translationOverride->getLabelIdentifier()][$translationOverride->getLocaleIdentifier()] = $translationOverride->getTranslation();
        }
        return $overrides;
    }

    /**
     * Flush the caches of this translation source
     */
    public function flushCaches(): void
    {
        // Clear the translation cache for this specific source
        $cacheIdentifier = md5($this->identifier);
        if ($this->translationCache->has($cacheIdentifier)) {
            $this->translationCache->remove($cacheIdentifier);
        }

        // Clear Fusion caches to ensure translated content is regenerated
        // This is necessary because translations are used in Fusion prototypes
        // that get cached separately from the translation data itself
        if ($this->cacheManager->hasCache('Neos_Neos_Fusion')) {
            $this->cacheManager->getCache('Neos_Neos_Fusion')->flush();
        }
        if ($this->cacheManager->hasCache('Neos_Fusion_Content')) {
            $this->cacheManager->getCache('Neos_Fusion_Content')->flush();
        }
    }
}
