<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 * @ORM\Table(
 *    uniqueConstraints={
 *      @ORM\UniqueConstraint(name="source_label_locale_identifier",columns={"sourceIdentifier", "labelIdentifier", "localeIdentifier"})
 *    },
 *    indexes={
 *      @ORM\Index(name="sourceIdentifier_index",columns={"sourceIdentifier"},options={"lengths": {255}})
 *    }
 * )*
 */
class TranslationOverride
{
    /**
     * @var string
     */
    protected $sourceIdentifier;

    /**
     * @var string
     */
    protected $labelIdentifier;

    /**
     * @var string
     */
    protected $localeIdentifier;

    /**
     * @var string
     * @ORM\Column(name="translation", type="text")
     */
    protected $translation;

    /**
     * @return string
     */
    public function getSourceIdentifier(): string
    {
        return $this->sourceIdentifier;
    }

    /**
     * @param string $sourceIdentifier
     */
    public function setSourceIdentifier(string $sourceIdentifier): void
    {
        $this->sourceIdentifier = $sourceIdentifier;
    }

    /**
     * @return string
     */
    public function getLabelIdentifier(): string
    {
        return $this->labelIdentifier;
    }

    /**
     * @param string $labelIdentifier
     */
    public function setLabelIdentifier(string $labelIdentifier): void
    {
        $this->labelIdentifier = $labelIdentifier;
    }

    /**
     * @return string
     */
    public function getLocaleIdentifier(): string
    {
        return $this->localeIdentifier;
    }

    /**
     * @param string $localeIdentifier
     */
    public function setLocaleIdentifier(string $localeIdentifier): void
    {
        $this->localeIdentifier = $localeIdentifier;
    }

    /**
     * @return string
     */
    public function getTranslation(): string
    {
        return $this->translation;
    }

    /**
     * @param string $translation
     */
    public function setTranslation(string $translation): void
    {
        $this->translation = $translation;
    }
}
