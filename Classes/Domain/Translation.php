<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\FormatResolver;

class Translation
{
    /**
     * @var string
     */
    protected $translation;

    /**
     * @var string
     */
    protected $override;

    /**
     * @var string
     */
    protected $localeIdentifier;

    /**
     * @var FormatResolver
     * @Flow\Inject
     */
    protected $formatResolver;

    public function __construct(string $localeIdentifier, string $translation = null , string $override = null)
    {
        $this->translation = $translation;
        $this->override = $override;
        $this->localeIdentifier = $localeIdentifier;
    }

    /**
     * @return string
     */
    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    /**
     * @return string
     */
    public function getOverride(): ?string
    {
        return $this->override;
    }

    /**
     * @return string
     */
    public function getLocaleIdentifier(): string
    {
        return $this->localeIdentifier;
    }

    /**
     * @param array|null $arguments
     */
    public function translate(array $arguments = null): string
    {
        if ($arguments) {
            return $this->formatResolver->resolvePlaceholders($this->override ?? $this->translation, $arguments);
        } else {
            return $this->override ?? $this->translation;
        }
    }

}
