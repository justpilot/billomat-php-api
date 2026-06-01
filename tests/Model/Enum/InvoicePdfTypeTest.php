<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoicePdfType::class)]
final class InvoicePdfTypeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedLowercaseApiValues(): void
    {
        // Billomat erwartet hier kleinbuchstaben: ?type=signed
        self::assertSame('signed', InvoicePdfType::SIGNED->value);
        self::assertSame('print', InvoicePdfType::PRINT->value);
    }

    #[Test]
    public function fromApiHandlesNullEmptyAndUnknown(): void
    {
        self::assertNull(InvoicePdfType::fromApi(null));
        self::assertNull(InvoicePdfType::fromApi(''));
        self::assertNull(InvoicePdfType::fromApi('BOGUS'));
        self::assertSame(InvoicePdfType::SIGNED, InvoicePdfType::fromApi('signed'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (InvoicePdfType::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
