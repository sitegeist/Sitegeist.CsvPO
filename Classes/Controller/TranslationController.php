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
use Sitegeist\CsvPO\Domain\TranslationOverrideRepository;
use Sitegeist\CsvPO\Domain\TranslationOverride;


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
     * @Flow\InjectConfiguration(path="management.locales")
     */
    protected $locales;

    /**
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationLabelSourceRepository;

    /**
     * @var TranslationOverrideRepository
     * @Flow\Inject
     */
    protected $translationOverrideRepository;

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

    public function showAction(string $sourceIdentifier)
    {
        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($sourceIdentifier);

        $translationsByLocale = [];
        foreach ($translationLabelSource->findAllTranslationLabels() as $translationLabel) {
            foreach ($this->locales as $localeIdentifier) {
                $localeChain = $this->localizationService->getLocaleChain( new Locale($localeIdentifier) );
                $translationsByLocale[$translationLabel->getIdentifier()][$localeIdentifier] = $translationLabel->getTranslation($localeIdentifier, $localeChain);
            }
        }

        $this->view->assign('source', $translationLabelSource);
        $this->view->assign('locales', $this->locales);
        $this->view->assign('translationsByLocale', $translationsByLocale);
    }

    /**
     * @param string $sourceIdentifier
     * @param string $localeIdentifier
     * @param string $labelIdentifier
     */
    public function newOverrideAction(string $sourceIdentifier, string $localeIdentifier, string $labelIdentifier) {
        $localeChain = $this->localizationService->getLocaleChain( new Locale($localeIdentifier) );

        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($sourceIdentifier);
        $label = $translationLabelSource->findTranslationLabelByIdentifier($labelIdentifier);
        $translation = $label->getTranslation($localeIdentifier, $localeChain);

        $translationOverride = new TranslationOverride();
        $translationOverride->setSourceIdentifier($sourceIdentifier);
        $translationOverride->setLabelIdentifier($labelIdentifier);
        $translationOverride->setLocaleIdentifier($localeIdentifier);
        $translationOverride->setTranslation($translation->getTranslation() ?? '');

        $this->view->assign('source', $translationLabelSource);
        $this->view->assign('translation', $translation);
        $this->view->assign('translationOverride', $translationOverride);
   }

    /**
     * @param TranslationOverride $translationLabel
     */
    public function addOverrideAction(TranslationOverride $translationOverride) {
        $this->translationOverrideRepository->add($translationOverride);
        $this->translationCache->flushByTag(md5($translationOverride->getSourceIdentifier()));
        $this->forward('show', null, null, ['sourceIdentifier' => $translationOverride->getSourceIdentifier()]);
    }

    /**
     * @param TranslationOverride $translationOverride
     */
    public function updateOverrideAction( string $sourceIdentifier, string $localeIdentifier, string $labelIdentifier )
    {
        $translationOverride = $this->translationOverrideRepository->findOneSpecific($sourceIdentifier, $localeIdentifier, $labelIdentifier);
        $localeChain = $this->localizationService->getLocaleChain( new Locale($translationOverride->getLocaleIdentifier()) );

        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($translationOverride->getSourceIdentifier());
        $label = $translationLabelSource->findTranslationLabelByIdentifier($translationOverride->getLabelIdentifier());
        $translation = $label->getTranslation($localeIdentifier, $localeChain);

        $this->view->assign('source', $translationLabelSource);
        $this->view->assign('translation', $translation);
        $this->view->assign('translationOverride', $translationOverride);
        $this->view->assign('translationLabel', $translationOverride);
    }

    /**
     * @param TranslationOverride $translationLabel
     */
    public function saveOverrideAction(TranslationOverride $translationOverride)
    {
        $this->translationOverrideRepository->update($translationOverride);
        $this->translationCache->flushByTag(md5($translationOverride->getSourceIdentifier()));
        $this->forward('show', null, null, ['sourceIdentifier' => $translationOverride->getSourceIdentifier()]);
    }

    /**
     * @param TranslationOverride $translationLabel
     */
    public function deleteOverrideAction(TranslationOverride $translationOverride)
    {
        $this->translationOverrideRepository->remove($translationOverride);
        $this->translationCache->flushByTag(md5($translationOverride->getSourceIdentifier()));
        $this->forward('show', null, null, ['sourceIdentifier' => $translationOverride->getSourceIdentifier()]);
    }
}
