<?php
declare(strict_types=1);

namespace Sitegeist\CsvPO\TranslationProvider;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\TranslationProvider\TranslationProviderInterface;
use Sitegeist\CsvPO\Domain\TranslationLabelSource;
use Sitegeist\CsvPO\Domain\TranslationLabelSourceRepository;
use Sitegeist\CsvPO\Eel\TranslationSourceConnector;
use Neos\Flow\I18n\Service as LocalizationService;

class CsvPoTranslationProvider implements TranslationProviderInterface
{

    /**
     * @var TranslationLabelSourceRepository
     * @Flow\Inject
     */
    protected $translationLabelSourceRepository;

    /**
     * @var LocalizationService
     * @Flow\Inject
     */
    protected $localisationService;

    /**
     * @var array<string,TranslationSourceConnector>
     */
    protected $translationSourceCache = [];

    /**
     * @var mixed[]
     */
    protected $localeFallbackChainCache = [];

    public function getTranslationByOriginalLabel($originalLabel, Locale $locale, $pluralForm = null, $sourceName = 'Main', $packageKey = 'Neos.Flow')
    {
        return $this->getTranslationById($originalLabel, $locale, $pluralForm, $sourceName, $packageKey);
    }

    public function getTranslationById($labelId, Locale $locale, $pluralForm = null, $sourceName = 'Main', $packageKey = 'Neos.Flow')
    {
        $source = $this->findTranslationSource($sourceName, $packageKey);
        if ($source) {
            $localeChain = $this->getFallbackChain($locale);
            $label = $source->findTranslationLabelByIdentifier($labelId);
            if ($label) {
                return $label->findTranslationForLocaleChain($localeChain);
            }
        }
        return 'dasdas' . $labelId;
    }

    protected function getFallbackChain(Locale $locale): array
    {
        $identifier = (string)$locale;
        if (!array_key_exists($identifier, $this->localeFallbackChainCache)) {
            $this->localeFallbackChainCache[$identifier] = $this->localisationService->getLocaleChain($locale);
        }
        return $this->localeFallbackChainCache[$identifier];
    }

    protected function findTranslationSource(string $sourceName, string $packageKey): ?TranslationLabelSource
    {
        $identifier = 'resource://' . $packageKey . '/' . $sourceName;
        if (!array_key_exists($identifier, $this->translationSourceCache)) {
            $this->translationSourceCache[$identifier] = $this->translationLabelSourceRepository->findOneByIdentifier($identifier);
        }
        return $this->translationSourceCache[$identifier];
    }
}
