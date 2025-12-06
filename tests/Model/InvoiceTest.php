<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

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
}