<?php

namespace Sitegeist\CsvPO\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Locale;
use Neos\Flow\Security\Exception\AccessDeniedException;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Flow\Package\PackageManager;
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
     * @var bool
     * @Flow\InjectConfiguration(path="management.enabled")
     */
    protected $managementEnabled;

    /**
     * @var string[]
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

    public function initializeAction()
    {
        if (!$this->managementEnabled) {
            throw new AccessDeniedException("Translation management is disabled");
        }
        parent::initializeAction();
    }

    public function indexAction(): void
    {
        $translationLabelSources = $this->translationLabelSourceRepository->findAll();
        $translationLabelSourcesGroupedByPackageKey = [];
        foreach ($translationLabelSources as $translationLabelSource) {
            if (!array_key_exists($translationLabelSource->getPackageKey(), $translationLabelSourcesGroupedByPackageKey)) {
                $translationLabelSourcesGroupedByPackageKey[$translationLabelSource->getPackageKey()] = [];
            }
            $translationLabelSourcesGroupedByPackageKey[$translationLabelSource->getPackageKey()][] = $translationLabelSource;
        }
        $this->view->assign('translationLabelSources', $translationLabelSources);
        $this->view->assign('translationLabelSourcesGroupedByPackageKey', $translationLabelSourcesGroupedByPackageKey);
    }

    public function showAction(string $sourceIdentifier): void
    {
        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($sourceIdentifier);
        $translationsByLocale = [];

        if ($translationLabelSource) {
            foreach ($translationLabelSource->findAllTranslationLabels() as $translationLabel) {
                $labelInformations = [
                    'identifier' => $translationLabel->getIdentifier(),
                    'description' => $translationLabel->getDescription(),
                    'translations' => []
                ];
                foreach ($this->locales as $localeIdentifier) {
                    $localeChain = $this->localizationService->getLocaleChain(new Locale($localeIdentifier));
                    $labelInformations['translations'][$localeIdentifier] = $translationLabel->findTranslationForLocaleChain($localeChain);
                }
                $translationsByLocale[$translationLabel->getIdentifier()] = $labelInformations;
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
    public function newOverrideAction(string $sourceIdentifier, string $localeIdentifier, string $labelIdentifier): void
    {
        $localeChain = $this->localizationService->getLocaleChain(new Locale($localeIdentifier));

        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($sourceIdentifier);
        $translation = null;
        $translationOverride = null;

        if ($translationLabelSource) {
            $label = $translationLabelSource->findTranslationLabelByIdentifier($labelIdentifier);
            if ($label) {
                $translation = $label->findTranslationForLocaleChain($localeChain);
                $translationOverride = new TranslationOverride();
                $translationOverride->setSourceIdentifier($sourceIdentifier);
                $translationOverride->setLabelIdentifier($labelIdentifier);
                $translationOverride->setLocaleIdentifier($localeIdentifier);
                $translationOverride->setTranslation($translation->getTranslation() ?? '');
            }
        }

        $this->view->assign('source', $translationLabelSource);
        $this->view->assign('translation', $translation);
        $this->view->assign('translationOverride', $translationOverride);
    }

    public function addOverrideAction(TranslationOverride $translationOverride): void
    {
        $this->translationOverrideRepository->add($translationOverride);

        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($translationOverride->getSourceIdentifier());
        if ($translationLabelSource) {
            $translationLabelSource->flushCaches();
        }

        $this->redirect('show', null, null, ['sourceIdentifier' => $translationOverride->getSourceIdentifier()]);
    }

    public function updateOverrideAction(string $sourceIdentifier, string $localeIdentifier, string $labelIdentifier): void
    {
        $translationLabelSource = null;
        $translation = null;

        $translationOverride = $this->translationOverrideRepository->findOneSpecific($sourceIdentifier, $localeIdentifier, $labelIdentifier);
        if ($translationOverride) {
            $localeChain = $this->localizationService->getLocaleChain(new Locale($translationOverride->getLocaleIdentifier()));
            $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($translationOverride->getSourceIdentifier());
            if ($translationLabelSource) {
                $label = $translationLabelSource->findTranslationLabelByIdentifier($translationOverride->getLabelIdentifier());
                if ($label) {
                    $translation = $label->findTranslationForLocaleChain($localeChain);
                }
            }
        }

        $this->view->assign('source', $translationLabelSource);
        $this->view->assign('translation', $translation);
        $this->view->assign('translationOverride', $translationOverride);
        $this->view->assign('translationLabel', $translationOverride);
    }

    public function saveOverrideAction(TranslationOverride $translationOverride): void
    {
        $this->translationOverrideRepository->update($translationOverride);

        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($translationOverride->getSourceIdentifier());
        if ($translationLabelSource) {
            $translationLabelSource->flushCaches();
        }
        $this->redirect('show', null, null, ['sourceIdentifier' => $translationOverride->getSourceIdentifier()]);
    }

    public function deleteOverrideAction(TranslationOverride $translationOverride): void
    {
        $translationLabelSource = $this->translationLabelSourceRepository->findOneByIdentifier($translationOverride->getSourceIdentifier());
        if ($translationLabelSource) {
            $this->translationOverrideRepository->remove($translationOverride);
            $translationLabelSource->flushCaches();
        }
        $this->redirect('show', null, null, ['sourceIdentifier' => $translationOverride->getSourceIdentifier()]);
    }
}
