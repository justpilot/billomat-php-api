<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceCommentActionKey::class)]
final class InvoiceCommentActionKeyTest extends TestCase
{
    #[Test]
    public function itParsesKnownActionKeys(): void
    {
        self::assertSame(InvoiceCommentActionKey::CREATE, InvoiceCommentActionKey::tryFrom('CREATE'));
        self::assertSame(InvoiceCommentActionKey::COMPLETE, InvoiceCommentActionKey::tryFrom('COMPLETE'));
        self::assertSame(InvoiceCommentActionKey::CHANGE_STATUS, InvoiceCommentActionKey::tryFrom('CHANGE_STATUS'));
    }

    #[Test]
    public function tryFromReturnsNullForUnknownValues(): void
    {
        self::assertNull(InvoiceCommentActionKey::tryFrom('NOT_A_REAL_ACTION'));
    }

    #[Test]
    public function casesAreUppercase(): void
    {
        foreach (InvoiceCommentActionKey::cases() as $case) {
            self::assertSame(strtoupper($case->value), $case->value);
        }
    }
}
