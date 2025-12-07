<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\InvoicePayment;
use PHPUnit\Framework\TestCase;

final class InvoicePaymentTest extends TestCase
{
    public function test_from_array_hydrates_invoice_payment(): void
    {
        $data = [
            'id' => '55',
            'invoice_id' => '789',
            'date' => '2025-03-10',
            'amount' => '119.90',
            'type' => 'BANK_TRANSFER',
            'comment' => 'Testzahlung',
        ];

        $payment = InvoicePayment::fromArray($data);

        self::assertInstanceOf(InvoicePayment::class, $payment);
        self::assertSame(55, $payment->id);
        self::assertSame(789, $payment->invoiceId);

        self::assertInstanceOf(\DateTimeImmutable::class, $payment->date);
        self::assertSame('2025-03-10', $payment->date?->format('Y-m-d'));

        self::assertSame(119.90, $payment->amount);
        self::assertSame(InvoicePaymentType::BANK_TRANSFER, $payment->type);
        self::assertSame('Testzahlung', $payment->comment);
    }

    public function test_to_array_exports_values(): void
    {
        $payment = new InvoicePayment(
            id: 10,
            invoiceId: 200,
            date: new \DateTimeImmutable('2025-03-01'),
            amount: 89.5,
            type: InvoicePaymentType::CASH,
            comment: 'Barzahlung',
        );

        $array = $payment->toArray();

        self::assertIsArray($array);

        self::assertSame(10, $array['id']);
        self::assertSame(200, $array['invoice_id']);
        self::assertSame('2025-03-01', $array['date']);
        self::assertSame(89.5, $array['amount']);
        self::assertSame('CASH', $array['type']);
        self::assertSame('Barzahlung', $array['comment']);
    }

    public function test_from_array_handles_missing_optional_fields(): void
    {
        $data = [
            'id' => 1,
            'invoice_id' => 2,
            'amount' => 10.0,
            // keine date, type, comment
        ];

        $payment = InvoicePayment::fromArray($data);

        self::assertSame(1, $payment->id);
        self::assertSame(2, $payment->invoiceId);
        self::assertSame(10.0, $payment->amount);

        self::assertNull($payment->date);
        self::assertNull($payment->type);
        self::assertNull($payment->comment);
    }
}