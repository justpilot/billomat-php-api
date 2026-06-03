<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\ContactUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactUpdateOptions::class)]
final class ContactUpdateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesNameAndWww(): void
    {
        $options = new ContactUpdateOptions();
        $options->name = 'Buchhaltung GmbH';
        $options->www = 'example.com';

        $payload = $options->toArray();

        self::assertSame('Buchhaltung GmbH', $payload['name']);
        self::assertSame('example.com', $payload['www']);
    }
}
