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
}
