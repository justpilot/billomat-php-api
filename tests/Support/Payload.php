<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Support;

use PHPUnit\Framework\Assert;

/**
 * Typisierter Zugriff auf verschachtelte Payload-Arrays (`toArray()`, dekodiertes JSON).
 *
 * Der Pfad wird Schritt für Schritt abgelaufen; ein Zwischenschritt, der kein Array ist,
 * lässt den Test mit einer lesbaren Meldung scheitern.
 */
final class Payload
{
    /**
     * Wert unter dem Pfad, oder null, wenn der letzte Schlüssel fehlt.
     *
     * @param array<array-key, mixed> $data
     */
    public static function at(array $data, string|int ...$path): mixed
    {
        $node = $data;
        foreach ($path as $step) {
            if (!\is_array($node)) {
                Assert::fail(\sprintf('Payload-Pfad "%s" ist nicht erreichbar.', implode('.', $path)));
            }

            $node = $node[$step] ?? null;
        }

        return $node;
    }

    /**
     * Array unter dem Pfad; scheitert, wenn dort kein Array liegt.
     *
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    public static function arrayAt(array $data, string|int ...$path): array
    {
        $node = [] === $path ? $data : self::at($data, ...$path);
        Assert::assertIsArray($node, \sprintf('Payload-Pfad "%s" ist kein Array.', implode('.', $path)));

        return $node;
    }
}
