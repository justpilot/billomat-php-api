<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\NetGross;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(NetGross::class)]
final class NetGrossTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCases(): void
    {
        self::assertSame(
            ['NET', 'GROSS', 'SETTINGS'],
            array_map(static fn (NetGross $c): string => $c->value, NetGross::cases())
        );
    }
}
