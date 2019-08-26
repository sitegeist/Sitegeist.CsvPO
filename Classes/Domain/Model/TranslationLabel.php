<?php
namespace Sitegeist\CsvPO\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 * @ORM\Table(
 *    uniqueConstraints={
 *      @ORM\UniqueConstraint(name="source_label_locale",columns={"source", "label", "locale"})
 *    },
 *    indexes={
 *      @ORM\Index(name="source_index",columns={"source"},options={"lengths": {255}})
 *    }
 * )*
 */
class TranslationLabel
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $translation;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
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
