<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use DateTimeImmutable;
use Justpilot\Billomat\Api\OfferUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferUpdateOptions::class)]
final class OfferUpdateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesValidityDate(): void
    {
        $options = new OfferUpdateOptions();
        $options->validityDate = new DateTimeImmutable('2026-12-31');

        self::assertSame('2026-12-31', $options->toArray()['validity_date']);
    }
}
