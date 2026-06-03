<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\OfferItemCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferItemCreateOptions::class)]
final class OfferItemCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesOptionalFlag(): void
    {
        $options = new OfferItemCreateOptions(quantity: 1.0, unitPrice: 100.0);
        $options->optional = 1;

        self::assertSame(1, $options->toArray()['optional']);
    }

    #[Test]
    public function itOmitsOptionalWhenNull(): void
    {
        $options = new OfferItemCreateOptions(quantity: 1.0, unitPrice: 100.0);

        self::assertArrayNotHasKey('optional', $options->toArray());
    }
}
