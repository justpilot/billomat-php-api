<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Support;

use PHPUnit\Framework\Assert;

use const JSON_THROW_ON_ERROR;

/**
 * Liest den JSON-Body eines vom `MockHttpClient` mitgeschnittenen Requests.
 *
 * Symfony legt ihn je nach Aufruf unter `$options['json']` oder als bereits
 * kodierten String unter `$options['body']` ab.
 */
final class JsonPayload
{
    /**
     * @param array<mixed> $options die `$options` des `MockHttpClient`-Callbacks
     *
     * @return array<string, mixed>
     */
    public static function fromOptions(array $options): array
    {
        $payload = $options['json'] ?? null;

        if (null === $payload) {
            $body = $options['body'] ?? null;
            if (\is_string($body) && '' !== $body) {
                $payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
            }
        }

        Assert::assertIsArray($payload, 'Expected JSON payload array (options[json] or decoded options[body]).');

        $record = [];
        foreach ($payload as $key => $value) {
            $record[(string) $key] = $value;
        }

        return $record;
    }
}
