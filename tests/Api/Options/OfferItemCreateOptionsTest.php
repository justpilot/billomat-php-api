<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferItemCreateOptions::class)]
#[UsesClass(Money::class)]
final class OfferItemCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesOptionalFlag(): void
    {
        $options = new OfferItemCreateOptions(quantity: 1.0, unitPrice: Money::of('100'), optional: 1);

        self::assertSame(1, $options->toArray()['optional']);
    }

    #[Test]
    public function itOmitsOptionalWhenNull(): void
    {
        $options = new OfferItemCreateOptions(quantity: 1.0, unitPrice: Money::of('100'));

        self::assertArrayNotHasKey('optional', $options->toArray());
    }
}
