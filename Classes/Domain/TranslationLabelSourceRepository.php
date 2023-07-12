<?php

namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Utility\Files;
use Sitegeist\CsvPO\Exception\TranslationLabelSourceNotFoundException;

class TranslationLabelSourceRepository
{
    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="management.packageKeys")
     */
    protected $packageKeys;

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
     * @var array
     * @Flow\InjectConfiguration(path="management.locales")
     */
    protected $locales;

    /**
     * @param $identifier
     * @return TranslationLabelSource|null
     * @throws TranslationLabelSourceNotFoundException
     */
    public function findOneByIdentifier($identifier): ?TranslationLabelSource
    {
        if (file_exists($identifier) && is_file($identifier)) {
            return new TranslationLabelSource($identifier);
        } else {
            throw new TranslationLabelSourceNotFoundException(sprintf('Translation source %s was not found', $identifier));
        }
    }

    /**
     * @return TranslationLabelSource[]
     */
    public function findAll(): array
    {
        $translationIdentifiers = [];
        foreach ($this->packageKeys as $packageKey) {
            $package = $this->packageManager->getPackage($packageKey);
            if ($package instanceof FlowPackageInterface) {
                $resourcesPath = $package->getResourcesPath();
                $packageTranslationFiles = Files::readDirectoryRecursively($resourcesPath . $this->resourcePath, $this->fileExtension);
                foreach ($packageTranslationFiles as $packageTranslationFile) {
                    $translationIdentifiers[] = str_replace($resourcesPath, 'resource://' . $packageKey . '/', $packageTranslationFile);
                }
            }
        }

        return array_map(
            function ($translationIdentifier) {
                return $this->findOneByIdentifier($translationIdentifier);
            },
            $translationIdentifiers
        );
    }
}
