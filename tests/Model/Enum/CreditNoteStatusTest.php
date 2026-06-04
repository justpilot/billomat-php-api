<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreditNoteStatus::class)]
final class CreditNoteStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        self::assertSame(
            ['DRAFT', 'OPEN', 'PAID', 'CANCELED'],
            array_map(static fn (CreditNoteStatus $c): string => $c->value, CreditNoteStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(CreditNoteStatus::fromApi(null));
        self::assertNull(CreditNoteStatus::fromApi('BOGUS'));
        self::assertSame(CreditNoteStatus::PAID, CreditNoteStatus::fromApi('PAID'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (CreditNoteStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
