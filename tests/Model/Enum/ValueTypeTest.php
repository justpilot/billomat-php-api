<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\ValueType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValueType::class)]
final class ValueTypeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCases(): void
    {
        self::assertSame(
            ['SETTINGS', 'ABSOLUTE', 'RELATIVE'],
            array_map(static fn (ValueType $c): string => $c->value, ValueType::cases())
        );
    }
}
