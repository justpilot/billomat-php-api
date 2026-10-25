<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\PaymentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PaymentType::class)]
final class PaymentTypeTest extends TestCase
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
            array_map(static fn (PaymentType $c): string => $c->value, PaymentType::cases())
        );
    }

    #[Test]
    public function fromApiReturnsNullForNullEmptyOrUnknown(): void
    {
        self::assertNull(PaymentType::fromApi(null));
        self::assertNull(PaymentType::fromApi(''));
        self::assertNull(PaymentType::fromApi('BOGUS'));
        self::assertSame(PaymentType::CASH, PaymentType::fromApi('CASH'));
    }

    #[Test]
    public function eachCaseHasGermanLabel(): void
    {
        foreach (PaymentType::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
