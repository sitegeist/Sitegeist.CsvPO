<?php
namespace Sitegeist\CsvPO\Controller;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Flow\Package\PackageManager;
use Neos\Utility\Files;
use Sitegeist\CsvPO\Domain\Model\TranslationLabel;
use Sitegeist\CsvPO\Service\TranslationService;
use Sitegeist\CsvPO\Domain\Repository\TranslationLabelRepository;

class TranslationOverrideController extends AbstractModuleController
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
     * @Flow\InjectConfiguration(path="management.locales")
     */
    protected $locales;

    /**
     * @var TranslationLabelRepository
     * @Flow\Inject
     */
    protected $translationLabelRepository;

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $translationCache;

    public function indexAction()
    {
        $packageSources = [];

        foreach ($this->packageKeys as $packageKey) {
            $package = $this->packageManager->getPackage($packageKey);
            if($package instanceof FlowPackageInterface) {
                $resourcesPath = $package->getResourcesPath();
                $translations = Files::readDirectoryRecursively($resourcesPath . 'Private/Fusion', '.csv');
                $translations = array_map(
                    function($path) use ($resourcesPath,$packageKey) {
                        return str_replace($resourcesPath, 'resource://'. $packageKey . '/' , $path);
                    },
                    $translations);
                $packageSources[$packageKey] = [
                    'package' => $package,
                    'sources' => $translations,
                    'base' => $resourcesPath
                ];
            } else {
                $packageSources[$packageKey] = [
                    'package' => $package,
                    'sources' => []
                ];
            }
        }
        $this->view->assign('packagesSources', $packageSources);

    }

    public function showSourceAction(string $source)
    {
        $defaultTranslator = new TranslationService($source);
        $labels = $defaultTranslator->getAllLabels();
        $translationsByLocale = [];

        foreach ($this->locales as $locale) {
            $translator = new TranslationService($source, $locale);
            foreach($labels as $label) {
                $translationsByLocale[$label][$locale]['result'] = $translator->translate($label, []);
            }
        }

        /**
         * @var TranslationLabel[] $overrides
         */
        $overrides = $this->translationLabelRepository->findBySource($source);
        foreach ($overrides as $override) {
            $translationsByLocale[$override->getLabel()][$override->getLocale()]['override'] = $override;
        }

        $this->view->assign('source', $source);
        $this->view->assign('locales', $this->locales);
        $this->view->assign('translations', $defaultTranslator->getAll());
        $this->view->assign('translationsByLocale', $translationsByLocale);
    }

    /**
     * @param string $packageKey
     * @param string $source
     * @param string $locale
     * @param string $label
     */
    public function newAction(string $source, string $locale, string $label) {
        $translator = new TranslationService($source, $locale);

        $translationLabel = new TranslationLabel();
        $translationLabel->setLocale($locale);
        $translationLabel->setSource($source);
        $translationLabel->setLabel($label);
        $translationLabel->setTranslation($translator->translate($label, []));

        $this->view->assign('translationLabel', $translationLabel);
   }

    /**
     * @param TranslationLabel $translationLabel
     */
    public function addAction(TranslationLabel $translationLabel) {
        $this->translationLabelRepository->add($translationLabel);
        $this->translationCache->flushByTag(md5($translationLabel->getSource()));
        $this->forward('showSource', null, null, ['source' => $translationLabel->getSource()]);
    }

    /**
     * @param TranslationLabel $translationLabel
     */
    public function updateAction(TranslationLabel $translationLabel)
    {
        $this->view->assign('translationLabel', $translationLabel);
    }

    /**
     * @param TranslationLabel $translationLabel
     */
    public function saveAction(TranslationLabel $translationLabel)
    {
        $this->translationLabelRepository->update($translationLabel);
        $this->translationCache->flushByTag(md5($translationLabel->getSource()));
        $this->forward('showSource', null, null, ['source' => $translationLabel->getSource()]);
    }
}
