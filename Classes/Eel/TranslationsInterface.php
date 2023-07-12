<?php

declare(strict_types=1);

namespace Sitegeist\CsvPO\Eel;

use Neos\Eel\ProtectedContextAwareInterface;

/**
 * @extends \ArrayAccess<mixed, string>
 */
interface TranslationsInterface extends ProtectedContextAwareInterface, \JsonSerializable, \ArrayAccess
{
    /**
     * @param array<string|int, mixed> $arguments
     */
    public function __call(string $translationIdentifier, array $arguments = []): string;

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function getTranslationForIdentifier(string $translationIdentifier, array $arguments = []): string;
}
