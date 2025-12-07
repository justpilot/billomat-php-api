<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceItem;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
{
    public function test_it_hydrates_invoice_with_items_from_array(): void
    {
        $data = [
            'id' => '123',
            'client_id' => '456',
            'contact_id' => '789',
            'invoice_number' => 'RE-2025-0001',
            'date' => '2025-03-01',
            'due_date' => '2025-03-15',
            'currency_code' => 'EUR',
            'status' => 'OPEN',
            'total_gross' => '119.0',
            'total_net' => '100.0',
            'invoice-items' => [
                'invoice-item' => [
                    [
                        'id' => '1',
                        'invoice_id' => '123',
                        'article_id' => '42',
                        'position' => '1',
                        'unit' => 'Stück',
                        'quantity' => '2',
                        'unit_price' => '50',
                        'tax_name' => 'USt 19%',
                        'tax_rate' => '19',
                        'tax_changed_manually' => '0',
                        'title' => 'Testposition',
                        'description' => 'Eine Testposition',
                        'reduction' => '10%',
                        'type' => 'SERVICE',
                        'total_gross' => '119.0',
                        'total_net' => '100.0',
                        'total_gross_unreduced' => '130.0',
                        'total_net_unreduced' => '110.0',
                        'created' => '2025-03-01T12:00:00+01:00',
                    ],
                ],
            ],
        ];

        $invoice = Invoice::fromArray($data);

        self::assertInstanceOf(Invoice::class, $invoice);
        self::assertSame(123, $invoice->id);
        self::assertSame(456, $invoice->clientId);
        self::assertSame(789, $invoice->contactId);
        self::assertSame('RE-2025-0001', $invoice->invoiceNumber);
        self::assertSame('EUR', $invoice->currencyCode);
        self::assertSame(119.0, $invoice->totalGross);
        self::assertSame(100.0, $invoice->totalNet);

        self::assertInstanceOf(\DateTimeImmutable::class, $invoice->date);
        self::assertSame('2025-03-01', $invoice->date?->format('Y-m-d'));

        self::assertInstanceOf(\DateTimeImmutable::class, $invoice->dueDate);
        self::assertSame('2025-03-15', $invoice->dueDate?->format('Y-m-d'));

        self::assertInstanceOf(InvoiceStatus::class, $invoice->status);
        self::assertSame(InvoiceStatus::OPEN, $invoice->status);

        // Items
        self::assertIsArray($invoice->items);
        self::assertCount(1, $invoice->items);
        self::assertContainsOnlyInstancesOf(InvoiceItem::class, $invoice->items);

        /** @var InvoiceItem $item */
        $item = $invoice->items[0];

        self::assertSame(1, $item->id);
        self::assertSame(123, $item->invoiceId);
        self::assertSame(42, $item->articleId);
        self::assertSame(1, $item->position);
        self::assertSame('Stück', $item->unit);

        self::assertSame(2.0, $item->quantity);
        self::assertSame(50.0, $item->unitPrice);

        self::assertSame('USt 19%', $item->taxName);
        self::assertSame(19.0, $item->taxRate);
        self::assertFalse($item->taxChangedManually);

        self::assertSame('Testposition', $item->title);
        self::assertSame('Eine Testposition', $item->description);
        self::assertSame('10%', $item->reduction);

        self::assertInstanceOf(InvoiceItemType::class, $item->type);
        self::assertSame(InvoiceItemType::SERVICE, $item->type);

        self::assertSame(119.0, $item->totalGross);
        self::assertSame(100.0, $item->totalNet);
        self::assertSame(130.0, $item->totalGrossUnreduced);
        self::assertSame(110.0, $item->totalNetUnreduced);

        self::assertInstanceOf(\DateTimeImmutable::class, $item->created);
        self::assertSame(
            '2025-03-01T12:00:00+01:00',
            $item->created?->format('Y-m-d\TH:i:sP')
        );
    }

    public function test_it_hydrates_invoice_without_items_to_empty_items_array(): void
    {
        $data = [
            'id' => '123',
            'client_id' => '456',
            'status' => 'DRAFT',
            'currency_code' => 'EUR',
        ];

        $invoice = Invoice::fromArray($data);

        self::assertInstanceOf(Invoice::class, $invoice);
        self::assertSame(123, $invoice->id);
        self::assertSame(456, $invoice->clientId);
        self::assertSame('EUR', $invoice->currencyCode);
        self::assertSame(InvoiceStatus::DRAFT, $invoice->status);

        self::assertIsArray($invoice->items);
        self::assertCount(0, $invoice->items);
    }

    public function test_it_handles_single_item_array_shape_from_api(): void
    {
        // Billomat liefert bei genau einer Position manchmal kein numerisches Array,
        // sondern direkt ein assoziatives Array mit einem "invoice-item".
        $data = [
            'id' => '123',
            'client_id' => '456',
            'status' => 'OPEN',
            'currency_code' => 'EUR',
            'invoice-items' => [
                'invoice-item' => [
                    'id' => '1',
                    'invoice_id' => '123',
                    'quantity' => '1',
                    'unit_price' => '100',
                    'type' => 'PRODUCT',
                ],
            ],
        ];

        $invoice = Invoice::fromArray($data);

        self::assertIsArray($invoice->items);
        self::assertCount(1, $invoice->items);
        self::assertInstanceOf(InvoiceItem::class, $invoice->items[0]);

        $item = $invoice->items[0];
        self::assertSame(1, $item->id);
        self::assertSame(123, $item->invoiceId);
        self::assertSame(1.0, $item->quantity);
        self::assertSame(100.0, $item->unitPrice);
        self::assertSame(InvoiceItemType::PRODUCT, $item->type);
    }

    public function test_it_hydrates_full_invoice_from_array(): void
    {
        $data = [
            'id' => '1',
            'client_id' => '123',
            'contact_id' => '',
            'created' => '2007-12-13T12:12:00+01:00',
            'invoice_number' => 'RE123',
            'number' => '123',
            'number_pre' => 'RE',
            'number_length' => '0',
            'status' => 'OPEN',
            'date' => '2009-10-14',
            'supply_date' => '2009-10-12',
            'supply_date_type' => 'SUPPLY_DATE',
            'due_date' => '2009-10-24',
            'due_days' => '10',
            'address' => "Billomat GmbH & Co. KG\nHollertszug 26\n57562 Herdorf\nDeutschland",
            'discount_rate' => '2.0',
            'discount_date' => '2009-10-21',
            'discount_days' => '7',
            'discount_amount' => '2.0',
            'title' => '',
            'label' => 'Projekt 123',
            'intro' => 'Wir freuen uns, Ihnen folgende Positionen in Rechnung stellen zu dürfen:',
            'note' => 'Vielen Dank für Ihren Auftrag!',
            'total_gross' => '107.1',
            'total_net' => '90.0',
            'net_gross' => 'NET',
            'reduction' => '10',
            'total_gross_unreduced' => '119.0',
            'total_net_unreduced' => '100.0',
            'paid_amount' => '20.0',
            'open_amount' => '99.0',
            'currency_code' => 'EUR',
            'quote' => '1.0000',
            'invoice_id' => '',
            'offer_id' => '',
            'confirmation_id' => '7',
            'recurring_id' => '',
            'taxes' => [
                'tax' => [
                    'name' => 'MwSt',
                    'rate' => '19.0',
                    'amount' => '19.0',
                ],
            ],
            'payment_types' => 'CASH,BANK_TRANSFER,PAYPAL',
            'customerportal_url' => 'https://mybillomatid.billomat.net/customerportal/invoices/show/entityId/123?hash=123456789aabbcc',
            'template_id' => '',
        ];

        $invoice = Invoice::fromArray($data);

        self::assertSame(1, $invoice->id);
        self::assertSame(123, $invoice->clientId);
        self::assertNull($invoice->contactId);

        self::assertInstanceOf(\DateTimeImmutable::class, $invoice->created);
        self::assertSame('RE123', $invoice->invoiceNumber);
        self::assertSame(123, $invoice->number);
        self::assertSame('RE', $invoice->numberPre);
        self::assertSame(0, $invoice->numberLength);

        self::assertSame(InvoiceStatus::OPEN, $invoice->status);
        self::assertInstanceOf(\DateTimeImmutable::class, $invoice->date);
        self::assertInstanceOf(\DateTimeImmutable::class, $invoice->supplyDate);
        self::assertSame(SupplyDateType::SUPPLY_DATE, $invoice->supplyDateType);
        self::assertInstanceOf(\DateTimeImmutable::class, $invoice->dueDate);
        self::assertSame(10, $invoice->dueDays);

        self::assertSame($data['address'], $invoice->address);
        self::assertSame(2.0, $invoice->discountRate);
        self::assertInstanceOf(\DateTimeImmutable::class, $invoice->discountDate);
        self::assertSame(7, $invoice->discountDays);
        self::assertSame(2.0, $invoice->discountAmount);

        self::assertSame('', $invoice->title);
        self::assertSame('Projekt 123', $invoice->label);
        self::assertSame($data['intro'], $invoice->intro);
        self::assertSame($data['note'], $invoice->note);

        self::assertSame(107.1, $invoice->totalGross);
        self::assertSame(90.0, $invoice->totalNet);
        self::assertSame(NetGross::NET, $invoice->netGross);
        self::assertSame('10', $invoice->reduction);
        self::assertSame(119.0, $invoice->totalGrossUnreduced);
        self::assertSame(100.0, $invoice->totalNetUnreduced);

        self::assertSame(20.0, $invoice->paidAmount);
        self::assertSame(99.0, $invoice->openAmount);
        self::assertSame('EUR', $invoice->currencyCode);
        self::assertSame(1.0, $invoice->quote);

        self::assertNull($invoice->invoiceId);
        self::assertNull($invoice->offerId);
        self::assertSame(7, $invoice->confirmationId);
        self::assertNull($invoice->recurringId);

        self::assertSame('CASH,BANK_TRANSFER,PAYPAL', $invoice->paymentTypes);
        self::assertSame($data['customerportal_url'], $invoice->customerportalUrl);
        self::assertNull($invoice->templateId);

        // Taxes
        self::assertCount(1, $invoice->taxes);
        $firstTax = $invoice->taxes[0];
        self::assertSame('MwSt', $firstTax['name']);
        self::assertSame(19.0, $firstTax['rate']);
        self::assertSame(19.0, $firstTax['amount']);

        // Items ist in diesem Beispiel leer
        self::assertSame([], $invoice->items);
    }
}