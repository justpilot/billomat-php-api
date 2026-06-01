<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use DateTimeImmutable;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoicePaymentCreateOptions::class)]
final class InvoicePaymentCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadKeepsRequiredFields(): void
    {
        $options = new InvoicePaymentCreateOptions(invoiceId: 240, amount: 99.95);

        self::assertSame(
            ['invoice_id' => 240, 'amount' => 99.95, 'mark_invoice_as_paid' => 0],
            $options->toArray()
        );
    }

    #[Test]
    public function markInvoiceAsPaidIsSerializedAsOneOrZero(): void
    {
        $options = new InvoicePaymentCreateOptions(invoiceId: 1, amount: 1.0);
        $options->markInvoiceAsPaid = true;

        $payload = $options->toArray();

        self::assertSame(1, $payload['mark_invoice_as_paid']);
    }

    #[Test]
    public function itFormatsDateAsIsoLocalDate(): void
    {
        $options = new InvoicePaymentCreateOptions(invoiceId: 1, amount: 1.0);
        $options->date = new DateTimeImmutable('2024-06-01T12:34:56');

        $payload = $options->toArray();

        self::assertSame('2024-06-01', $payload['date']);
    }

    #[Test]
    public function itSerializesPaymentTypeEnumAsStringValue(): void
    {
        $options = new InvoicePaymentCreateOptions(invoiceId: 1, amount: 1.0);
        $options->type = InvoicePaymentType::BANK_TRANSFER;

        $payload = $options->toArray();

        self::assertSame('BANK_TRANSFER', $payload['type']);
    }

    #[Test]
    public function itStripsAllOtherNullFields(): void
    {
        $options = new InvoicePaymentCreateOptions(invoiceId: 1, amount: 1.0);
        $options->comment = 'manuell';

        $payload = $options->toArray();

        self::assertArrayNotHasKey('date', $payload);
        self::assertArrayNotHasKey('transaction_purpose', $payload);
        self::assertArrayNotHasKey('type', $payload);
        self::assertSame('manuell', $payload['comment']);
    }
}
