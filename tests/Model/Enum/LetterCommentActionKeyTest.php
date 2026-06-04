<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LetterCommentActionKey::class)]
final class LetterCommentActionKeyTest extends TestCase
{
    #[Test]
    public function itParsesKnownActionKeys(): void
    {
        self::assertSame(LetterCommentActionKey::CREATE, LetterCommentActionKey::tryFrom('CREATE'));
        self::assertSame(LetterCommentActionKey::UPLOAD, LetterCommentActionKey::tryFrom('UPLOAD'));
        self::assertSame(LetterCommentActionKey::CLEAR, LetterCommentActionKey::tryFrom('CLEAR'));
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValues(): void
    {
        self::assertNull(LetterCommentActionKey::tryFrom('NOT_A_REAL_ACTION'));
    }

    #[Test]
    public function casesAreUppercase(): void
    {
        foreach (LetterCommentActionKey::cases() as $case) {
            self::assertSame(strtoupper($case->value), $case->value);
        }
    }
}
