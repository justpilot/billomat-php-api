<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeliveryNoteStatus::class)]
final class DeliveryNoteStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        self::assertSame(
            ['DRAFT', 'OPEN', 'CLEARED', 'CANCELED'],
            array_map(static fn (DeliveryNoteStatus $c): string => $c->value, DeliveryNoteStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(DeliveryNoteStatus::fromApi(null));
        self::assertNull(DeliveryNoteStatus::fromApi('BOGUS'));
        self::assertSame(DeliveryNoteStatus::CLEARED, DeliveryNoteStatus::fromApi('CLEARED'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (DeliveryNoteStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
