<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferCommentActionKey::class)]
final class OfferCommentActionKeyTest extends TestCase
{
    #[Test]
    public function itParsesKnownActionKeys(): void
    {
        self::assertSame(OfferCommentActionKey::CREATE, OfferCommentActionKey::tryFrom('CREATE'));
        self::assertSame(OfferCommentActionKey::WIN, OfferCommentActionKey::tryFrom('WIN'));
        self::assertSame(OfferCommentActionKey::LOSE, OfferCommentActionKey::tryFrom('LOSE'));
        self::assertSame(OfferCommentActionKey::CLEAR, OfferCommentActionKey::tryFrom('CLEAR'));
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValues(): void
    {
        self::assertNull(OfferCommentActionKey::tryFrom('NOT_A_REAL_ACTION'));
    }

    #[Test]
    public function casesAreUppercase(): void
    {
        foreach (OfferCommentActionKey::cases() as $case) {
            self::assertSame(strtoupper($case->value), $case->value);
        }
    }
}
