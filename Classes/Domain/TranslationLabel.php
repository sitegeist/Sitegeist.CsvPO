<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\FormatResolver;
use Neos\Flow\I18n\Locale;

class TranslationLabel
{
    protected $identifier;

    protected $translations;

    protected $overrides;

    /**
     * @var FormatResolver
     * @Flow\Inject
     */
    protected $formatResolver;

    public function __construct(string $identifier, array $translations = [], array $overrides = [])
    {
        $this->identifier = $identifier;
        $this->translations = $translations;
        $this->overrides = $overrides;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param array|null $arguments
     * @param Locale[] $localeChain
     */
    public function translate(array $arguments = null, array $localeChain = null)
    {
        foreach ($localeChain as $localeIdentifier => $locale) {
            $translation = $this->overrides[$localeIdentifier] ?? ($this->translations[$localeIdentifier] ?? '');
            if ($translation) {
                break;
            }
        }
        if (count($arguments) > 0) {
            return $this->formatResolver->resolvePlaceholders($translation, $arguments[0]);
        } else {
            return $translation;
        }
    }
}
