<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\ReminderStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReminderStatus::class)]
final class ReminderStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        self::assertSame(
            ['DRAFT', 'OPEN', 'OVERDUE', 'PAID', 'CANCELED'],
            array_map(static fn (ReminderStatus $c): string => $c->value, ReminderStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(ReminderStatus::fromApi(null));
        self::assertNull(ReminderStatus::fromApi('BOGUS'));
        self::assertSame(ReminderStatus::OVERDUE, ReminderStatus::fromApi('OVERDUE'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (ReminderStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
