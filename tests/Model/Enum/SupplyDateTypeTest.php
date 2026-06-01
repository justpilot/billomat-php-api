<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\SupplyDateType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SupplyDateType::class)]
final class SupplyDateTypeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCases(): void
    {
        self::assertSame(
            ['SUPPLY_DATE', 'DELIVERY_DATE', 'SUPPLY_TEXT', 'DELIVERY_TEXT'],
            array_map(static fn (SupplyDateType $c): string => $c->value, SupplyDateType::cases())
        );
    }
}
