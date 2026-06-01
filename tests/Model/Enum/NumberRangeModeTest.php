<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\NumberRangeMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberRangeMode::class)]
final class NumberRangeModeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCases(): void
    {
        self::assertSame(
            ['IGNORE_PREFIX', 'CONSIDER_PREFIX'],
            array_map(static fn (NumberRangeMode $c): string => $c->value, NumberRangeMode::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(NumberRangeMode::fromApi(null));
        self::assertNull(NumberRangeMode::fromApi('BOGUS'));
        self::assertSame(
            NumberRangeMode::IGNORE_PREFIX,
            NumberRangeMode::fromApi('IGNORE_PREFIX')
        );
    }
}
