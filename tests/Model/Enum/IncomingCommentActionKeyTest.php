<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncomingCommentActionKey::class)]
final class IncomingCommentActionKeyTest extends TestCase
{
    #[Test]
    public function itParsesKnownActionKeys(): void
    {
        self::assertSame(IncomingCommentActionKey::CREATE, IncomingCommentActionKey::tryFrom('CREATE'));
        self::assertSame(IncomingCommentActionKey::UPLOAD, IncomingCommentActionKey::tryFrom('UPLOAD'));
        self::assertSame(IncomingCommentActionKey::PAYMENT, IncomingCommentActionKey::tryFrom('PAYMENT'));
        self::assertSame(IncomingCommentActionKey::UNCANCEL, IncomingCommentActionKey::tryFrom('UNCANCEL'));
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValues(): void
    {
        self::assertNull(IncomingCommentActionKey::tryFrom('NOT_A_REAL_ACTION'));
    }

    #[Test]
    public function casesAreUppercase(): void
    {
        foreach (IncomingCommentActionKey::cases() as $case) {
            self::assertSame(strtoupper($case->value), $case->value);
        }
    }
}
