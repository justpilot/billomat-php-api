<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreditNoteCommentActionKey::class)]
final class CreditNoteCommentActionKeyTest extends TestCase
{
    #[Test]
    public function itParsesKnownActionKeys(): void
    {
        self::assertSame(CreditNoteCommentActionKey::CREATE, CreditNoteCommentActionKey::tryFrom('CREATE'));
        self::assertSame(CreditNoteCommentActionKey::PAYMENT, CreditNoteCommentActionKey::tryFrom('PAYMENT'));
        self::assertSame(CreditNoteCommentActionKey::CANCEL, CreditNoteCommentActionKey::tryFrom('CANCEL'));
        self::assertSame(CreditNoteCommentActionKey::UNCANCEL, CreditNoteCommentActionKey::tryFrom('UNCANCEL'));
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValues(): void
    {
        self::assertNull(CreditNoteCommentActionKey::tryFrom('NOT_A_REAL_ACTION'));
    }

    #[Test]
    public function casesAreUppercase(): void
    {
        foreach (CreditNoteCommentActionKey::cases() as $case) {
            self::assertSame(strtoupper($case->value), $case->value);
        }
    }
}
