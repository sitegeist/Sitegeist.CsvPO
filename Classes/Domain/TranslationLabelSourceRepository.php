<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Utility\Files;
use Sitegeist\CsvPO\Exception\TranslationSourceNotFoundException;

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
     * @return TranslationLabelRepository|null
     * @throws TranslationSourceNotFoundException
     */
    public function findOneByIdentifier($identifier): ?TranslationLabelRepository
    {
        if (file_exists($identifier) && is_file($identifier)) {
            return new TranslationLabelRepository($identifier);
        } else {
            throw new TranslationSourceNotFoundException(sprintf('Translation source %s was not found', $identifier));
        }
    }

    /**
     * @return TranslationLabelRepository[]
     */
    public function findAll(): array
    {
        $translationIdentifiers = [];
        foreach ($this->packageKeys as $packageKey) {
            $package = $this->packageManager->getPackage($packageKey);
            if($package instanceof FlowPackageInterface) {
                $resourcesPath = $package->getResourcesPath();
                $packageTranslationFiles = Files::readDirectoryRecursively($resourcesPath . $this->resourcePath, $this->fileExtension);
                foreach($packageTranslationFiles as $packageTranslationFile) {
                    $translationIdentifiers[] = str_replace($resourcesPath, 'resource://'. $packageKey . '/' , $packageTranslationFile);
                }
            }
        }

        return array_map(
            function($translationIdentifier) {
                return $this->findOneByIdentifier($translationIdentifier);
            },
            $translationIdentifiers
        );

    }
}
