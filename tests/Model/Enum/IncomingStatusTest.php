<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\IncomingStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncomingStatus::class)]
final class IncomingStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        self::assertSame(
            ['DRAFT', 'OPEN', 'OVERDUE', 'PAID', 'CANCELED'],
            array_map(static fn (IncomingStatus $c): string => $c->value, IncomingStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(IncomingStatus::fromApi(null));
        self::assertNull(IncomingStatus::fromApi('BOGUS'));
        self::assertSame(IncomingStatus::OVERDUE, IncomingStatus::fromApi('OVERDUE'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (IncomingStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
