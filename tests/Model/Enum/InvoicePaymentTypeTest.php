<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoicePaymentType::class)]
final class InvoicePaymentTypeTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCasesAndApiValues(): void
    {
        // Werte werden 1:1 an Billomat geschickt — Drift = Regression.
        self::assertSame(
            [
                'INVOICE_CORRECTION',
                'CREDIT_NOTE',
                'BANK_CARD',
                'BANK_TRANSFER',
                'DEBIT',
                'CASH',
                'CHECK',
                'PAYPAL',
                'CREDIT_CARD',
                'COUPON',
                'MISC',
            ],
            array_map(static fn (InvoicePaymentType $c): string => $c->value, InvoicePaymentType::cases())
        );
    }

    #[Test]
    public function fromApiReturnsNullForNullEmptyOrUnknown(): void
    {
        self::assertNull(InvoicePaymentType::fromApi(null));
        self::assertNull(InvoicePaymentType::fromApi(''));
        self::assertNull(InvoicePaymentType::fromApi('BOGUS'));
        self::assertSame(InvoicePaymentType::CASH, InvoicePaymentType::fromApi('CASH'));
    }

    #[Test]
    public function eachCaseHasGermanLabel(): void
    {
        foreach (InvoicePaymentType::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
