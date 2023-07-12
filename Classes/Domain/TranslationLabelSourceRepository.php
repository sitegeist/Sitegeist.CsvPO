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
     * @var string[]
     * @Flow\InjectConfiguration(path="management.packageKeys")
     */
    protected $packageKeys;

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
     * @var string[]
     * @Flow\InjectConfiguration(path="management.locales")
     */
    protected $locales;

    public function findOneByIdentifier(string $identifier): ?TranslationLabelSource
    {
        if (file_exists($identifier) && is_file($identifier)) {
            return new TranslationLabelSource($identifier);
        } else {
            return null;
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

        return array_filter(array_map(
            function ($translationIdentifier) {
                return $this->findOneByIdentifier($translationIdentifier);
            },
            $translationIdentifiers
        ));
    }
}
