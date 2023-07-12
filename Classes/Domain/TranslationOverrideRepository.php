<?php

namespace Sitegeist\CsvPO\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class TranslationOverrideRepository extends Repository
{
    protected static string $ENTITY_CLASSNAME = TranslationOverride::class;

    public function findBySourceIdentifier(string $sourceIdentifier): QueryResultInterface
    {
        $query = $this->createQuery();
        $query = $query->matching($query->equals('sourceIdentifier', $sourceIdentifier));
        return $query->execute();
    }
    public function findOneSpecific(string $sourceIdentifier, string $localeIdentifier, string $labelIdentifier): ?TranslationOverride
    {
        $query = $this->createQuery();
        $query = $query->matching(
            $query->logicalAnd([
                $query->equals('sourceIdentifier', $sourceIdentifier),
                $query->equals('localeIdentifier', $localeIdentifier),
                $query->equals('labelIdentifier', $labelIdentifier)
            ])
        );
        $override = $query->execute()->getFirst();
        if ($override instanceof TranslationOverride) {
            return $override;
        } else {
            return null;
        }
    }
}
