<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 * @ORM\Table(
 *    uniqueConstraints={
 *      @ORM\UniqueConstraint(name="source_translation_locale_identifier",columns={"sourceIdentifier", "translationIdentifier", "localeIdentifier"})
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
    protected $translationIdentifier;

    /**
     * @var string
     */
    protected $localeIdentifier;

    /**
     * @var string
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
    public function getTranslationIdentifier(): string
    {
        return $this->translationIdentifier;
    }

    /**
     * @param string $translationIdentifier
     */
    public function setTranslationIdentifier(string $translationIdentifier): void
    {
        $this->translationIdentifier = $translationIdentifier;
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
