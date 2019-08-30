<?php
namespace Sitegeist\CsvPO\Controller;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Locale;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Flow\Package\PackageManager;
use Neos\Utility\Files;

use Neos\Flow\I18n\Service as LocalizationService;

use Sitegeist\CsvPO\Domain\TranslationLabelSourceRepository;


class TranslationController extends AbstractModuleController
{
    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localizationService;

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
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationLabelSourceRepository;

    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $translationCache;

    public function indexAction()
    {
        $translationLabelSources = $this->translationLabelSourceRepository->findAll();
        $this->view->assign('translationLabelSources', $translationLabelSources);
    }

    public function showSourceAction(string $translationLabelSourceIdentifier)
    {
        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($translationLabelSourceIdentifier);

        $translationsByLocale = [];
        foreach ($translationLabelSource->findAll() as $translationLabel) {
            foreach ($this->locales as $localeIdentifier) {
                $fallbackChain = $this->localizationService->getLocaleChain( new Locale($localeIdentifier) );
                $translationsByLocale[$translationLabel->getIdentifier()][$localeIdentifier]['result'] = $translationLabel->translate([], $fallbackChain);
            }
        }

        $this->view->assign('source', $translationLabelSource);
        $this->view->assign('locales', $this->locales);
        $this->view->assign('translationsByLocale', $translationsByLocale);
    }

    /**
     * @param string $packageKey
     * @param string $source
     * @param string $locale
     * @param string $label
     */
    public function newAction(string $source, string $locale, string $label) {
//        $translator = new TranslationService($source, $locale);
//
//        $translationLabel = new TranslationOverride();
//        $translationLabel->setLocale($locale);
//        $translationLabel->setSource($source);
//        $translationLabel->setLabel($label);
//        $translationLabel->setTranslation($translator->translate($label, []));
//
//        $this->view->assign('translationLabel', $translationLabel);
   }

    /**
     * @param TranslationOverride $translationLabel
     */
    public function addAction(TranslationOverride $translationLabel) {
//        $this->translationLabelRepository->add($translationLabel);
//        $this->translationCache->flushByTag(md5($translationLabel->getSource()));
//        $this->forward('showSource', null, null, ['source' => $translationLabel->getSource()]);
    }

    /**
     * @param TranslationOverride $translationLabel
     */
    public function updateAction(TranslationOverride $translationLabel)
    {
//        $this->view->assign('translationLabel', $translationLabel);
    }

    /**
     * @param TranslationOverride $translationLabel
     */
    public function saveAction(TranslationOverride $translationLabel)
    {
//        $this->translationLabelRepository->update($translationLabel);
//        $this->translationCache->flushByTag(md5($translationLabel->getSource()));
//        $this->forward('showSource', null, null, ['source' => $translationLabel->getSource()]);
    }
}
