<?php

namespace Sitegeist\CsvPO\Domain;

class Translation
{
    /**
     * @var string|null
     */
    protected $translation;

    /**
     * @var string|null
     */
    protected $override;

    /**
     * @var string|null
     */
    protected $fallback;

    /**
     * @var string|null
     */
    protected $fallbackLocaleIdentifier;

    public function __construct(
        string $translation = null,
        string $override = null,
        string $fallback = null,
        string $fallbackLocaleIdentifier = null
    ) {
        $this->translation = empty($translation) ? null : $translation;
        $this->override =  empty($override) ? null : $override;
        $this->fallback =  empty($fallback) ? null : $fallback;
        $this->fallbackLocaleIdentifier = $fallbackLocaleIdentifier;
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
    public function getFallback(): ?string
    {
        return $this->fallback;
    }

    /**
     * @return string
     */
    public function getFallbackLocaleIdentifier(): ?string
    {
        return $this->fallbackLocaleIdentifier;
    }

    /**
     * Get the current translation value, respects fallback and overrides
     * @return string
     */
    public function __toString(): string
    {
        return $this->override ?? $this->translation ?? $this->fallback ?? '';
    }
}
