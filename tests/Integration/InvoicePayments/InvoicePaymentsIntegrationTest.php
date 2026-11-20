<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\InvoicePayments;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class InvoicePaymentsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canCreatePaymentAndMarkInvoiceAsPaidInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen oder anlegen
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] === $clients) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
                countryCode: 'DE',
                email: $faker->unique()->safeEmail(),
            );

            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            title: 'Payment-Integrationstest '.date('d.m.Y H:i:s'),
            label: 'SDK Invoice Payment Test',
            currencyCode: 'EUR',
        );

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: Money::of((string) $faker->randomFloat(2, 20, 200)),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Zahlungstest-Position',
            description: 'Position für Payment-Flow',
        );

        $invoiceOpts = $invoiceOpts->withItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        $invoiceId = $draft->id;

        // 3) Rechnung abschließen (damit sie zahlbar ist)
        $billomat->invoices->complete($invoiceId);

        $completed = $billomat->invoices->get($invoiceId);
        self::assertInstanceOf(Invoice::class, $completed);
        self::assertNotSame(InvoiceStatus::DRAFT, $completed->status);

        // 4) Zahlung anlegen (voller Rechnungsbetrag, mark_invoice_as_paid = true)
        $amount = $completed->totalGross ?? $completed->totalNet ?? Money::of('10');

        $paymentOpts = new InvoicePaymentCreateOptions(
            amount: $amount,
            date: new DateTimeImmutable('today'),
            type: PaymentType::BANK_TRANSFER,
            comment: 'Integrationstest Zahlung',
            transactionPurpose: 'Testzahlung für Invoice #'.$invoiceId,
            markInvoiceAsPaid: true,
        );

        $payment = $billomat->invoices->payments->create($invoiceId, $paymentOpts);

        self::assertNotNull($payment->id);
        self::assertSame($invoiceId, $payment->invoiceId);
        self::assertTrue($amount->equals($payment->amount));

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
