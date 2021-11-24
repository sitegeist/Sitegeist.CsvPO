<?php
declare(strict_types=1);

namespace Sitegeist\CsvPO\TranslationProvider;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Locale;
use Neos\Flow\I18n\TranslationProvider\TranslationProviderInterface;
use Neos\Flow\I18n\TranslationProvider\XliffTranslationProvider;

class XliffAndCsvAwareTranslationProvider implements TranslationProviderInterface
{
    /**
     * @var XliffTranslationProvider
     * @Flow\Inject
     */
    protected $xliffTranslationProvoider;

    /**
     * @var CsvPoTranslationProvider
     * @Flow\Inject
     */
    protected $csvpoTranslationProvider;

    public function getTranslationByOriginalLabel($originalLabel, Locale $locale, $pluralForm = null, $sourceName = 'Main', $packageKey = 'Neos.Flow')
    {
        if (substr($sourceName, -4) === '.csv') {
            return $this->csvpoTranslationProvider->getTranslationByOriginalLabel($originalLabel, $locale, $pluralForm, $sourceName, $packageKey);
        } else {
            return $this->xliffTranslationProvoider->getTranslationByOriginalLabel($originalLabel, $locale, $pluralForm, $sourceName, $packageKey);
        }
    }

    public function getTranslationById($labelId, Locale $locale, $pluralForm = null, $sourceName = 'Main', $packageKey = 'Neos.Flow')
    {
        if (substr($sourceName, -4) === '.csv') {
            return $this->csvpoTranslationProvider->getTranslationById($labelId, $locale, $pluralForm, $sourceName, $packageKey);
        } else {
            return $this->xliffTranslationProvoider->getTranslationById($labelId, $locale, $pluralForm, $sourceName, $packageKey);
        }
    }
}
