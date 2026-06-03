<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use DateTimeImmutable;
use Justpilot\Billomat\Api\OfferCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferCreateOptions::class)]
final class OfferCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesValidityDateAsIsoDate(): void
    {
        $options = new OfferCreateOptions(clientId: 7);
        $options->validityDate = new DateTimeImmutable('2026-12-31 23:59:59');

        self::assertSame('2026-12-31', $options->toArray()['validity_date']);
    }

    #[Test]
    public function itOmitsValidityDateWhenNull(): void
    {
        $options = new OfferCreateOptions(clientId: 7);

        self::assertArrayNotHasKey('validity_date', $options->toArray());
    }
}
