<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\ContactCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactCreateOptions::class)]
final class ContactCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesNameAndWww(): void
    {
        $options = new ContactCreateOptions(clientId: 42);
        $options->name = 'Buchhaltung GmbH';
        $options->www = 'example.com';

        $payload = $options->toArray();

        self::assertSame('Buchhaltung GmbH', $payload['name']);
        self::assertSame('example.com', $payload['www']);
    }

    #[Test]
    public function minimalPayloadHasOnlyClientId(): void
    {
        $options = new ContactCreateOptions(clientId: 1);

        self::assertSame(['client_id' => 1], $options->toArray());
    }
}
