<?php
namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class TranslationOverrideRepository extends Repository
{
    protected static $ENTITY_CLASSNAME = TranslationOverride::class;

    public function findOneSpecific( string $sourceIdentifier, string $localeIdentifier, string $labelIdentifier )
    {
        $query = $this->createQuery();
        return $query->matching(
            $query->logicalAnd(
                $query->equals('sourceIdentifier', $sourceIdentifier),
                $query->equals('localeIdentifier', $localeIdentifier),
                $query->equals('labelIdentifier', $labelIdentifier)
            )
        )->execute()->getFirst();
    }

}
