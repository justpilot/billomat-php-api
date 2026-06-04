<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfirmationCommentActionKey::class)]
final class ConfirmationCommentActionKeyTest extends TestCase
{
    #[Test]
    public function itParsesKnownActionKeys(): void
    {
        self::assertSame(ConfirmationCommentActionKey::CREATE, ConfirmationCommentActionKey::tryFrom('CREATE'));
        self::assertSame(ConfirmationCommentActionKey::COMPLETE, ConfirmationCommentActionKey::tryFrom('COMPLETE'));
        self::assertSame(ConfirmationCommentActionKey::CLEAR, ConfirmationCommentActionKey::tryFrom('CLEAR'));
        self::assertSame(ConfirmationCommentActionKey::CHANGE_STATUS, ConfirmationCommentActionKey::tryFrom('CHANGE_STATUS'));
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValues(): void
    {
        self::assertNull(ConfirmationCommentActionKey::tryFrom('NOT_A_REAL_ACTION'));
    }

    #[Test]
    public function casesAreUppercase(): void
    {
        foreach (ConfirmationCommentActionKey::cases() as $case) {
            self::assertSame(strtoupper($case->value), $case->value);
        }
    }
}
