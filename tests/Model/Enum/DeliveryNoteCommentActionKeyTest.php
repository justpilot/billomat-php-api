<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeliveryNoteCommentActionKey::class)]
final class DeliveryNoteCommentActionKeyTest extends TestCase
{
    #[Test]
    public function itParsesKnownActionKeys(): void
    {
        self::assertSame(DeliveryNoteCommentActionKey::CREATE, DeliveryNoteCommentActionKey::tryFrom('CREATE'));
        self::assertSame(DeliveryNoteCommentActionKey::COMPLETE, DeliveryNoteCommentActionKey::tryFrom('COMPLETE'));
        self::assertSame(DeliveryNoteCommentActionKey::CLEAR, DeliveryNoteCommentActionKey::tryFrom('CLEAR'));
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValues(): void
    {
        self::assertNull(DeliveryNoteCommentActionKey::tryFrom('NOT_A_REAL_ACTION'));
    }

    #[Test]
    public function casesAreUppercase(): void
    {
        foreach (DeliveryNoteCommentActionKey::cases() as $case) {
            self::assertSame(strtoupper($case->value), $case->value);
        }
    }
}
