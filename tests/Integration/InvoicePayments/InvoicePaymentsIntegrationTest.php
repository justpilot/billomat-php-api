<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\InvoicePayments;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

final class InvoicePaymentsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    public function test_can_create_payment_and_mark_invoice_as_paid_in_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen oder anlegen
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ($clients === []) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
            );
            $clientOptions->email = $faker->unique()->safeEmail();
            $clientOptions->countryCode = 'DE';

            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(clientId: $clientId);
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'Payment-Integrationstest ' . date('d.m.Y H:i:s');
        $invoiceOpts->label = 'SDK Invoice Payment Test';

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 200),
        );
        $item->title = 'Zahlungstest-Position';
        $item->description = 'Position für Payment-Flow';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        /** @var Invoice $draft */
        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertInstanceOf(Invoice::class, $draft);
        self::assertNotNull($draft->id);

        $invoiceId = $draft->id;

        // 3) Rechnung abschließen (damit sie zahlbar ist)
        $completedResult = $billomat->invoices->complete($invoiceId);
        self::assertTrue($completedResult);

        $completed = $billomat->invoices->get($invoiceId);
        self::assertInstanceOf(Invoice::class, $completed);
        self::assertNotSame(InvoiceStatus::DRAFT, $completed->status);

        // 4) Zahlung anlegen (voller Rechnungsbetrag, mark_invoice_as_paid = true)
        $amount = $completed->totalGross ?? $completed->totalNet ?? 10.0;

        $paymentOpts = new InvoicePaymentCreateOptions(
            invoiceId: $invoiceId,
            amount: $amount,
        );
        $paymentOpts->date = new \DateTimeImmutable('today');
        $paymentOpts->type = InvoicePaymentType::BANK_TRANSFER;
        $paymentOpts->comment = 'Integrationstest Zahlung';
        $paymentOpts->transactionPurpose = 'Testzahlung für Invoice #' . $invoiceId;
        $paymentOpts->markInvoiceAsPaid = true;

        $payment = $billomat->invoicePayments->create($paymentOpts);

        self::assertInstanceOf(InvoicePayment::class, $payment);
        self::assertNotNull($payment->id);
        self::assertSame($invoiceId, $payment->invoiceId);
        self::assertSame($amount, $payment->amount);

        // 5) Rechnung erneut laden und sicherstellen, dass sie als bezahlt gilt
        $paidInvoice = $billomat->invoices->get($invoiceId);

        self::assertInstanceOf(Invoice::class, $paidInvoice);
        self::assertSame($invoiceId, $paidInvoice->id);
        self::assertNotNull($paidInvoice->status);
        self::assertSame(
            InvoiceStatus::PAID,
            $paidInvoice->status,
            'Invoice status should be PAID after full payment with mark_invoice_as_paid = true.'
        );
    }
}