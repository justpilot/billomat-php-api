<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class InvoicesApiTest extends TestCase
{
    public function test_it_lists_invoices_and_passes_filters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoices' => [
                    'invoice' => [
                        [
                            'id' => 1,
                            'client_id' => 123,
                            'status' => InvoiceStatus::DRAFT->value,
                            'invoice_number' => null,
                            'date' => '2025-01-01',
                            'due_date' => '2025-01-15',
                            'currency_code' => 'EUR',
                            'total_gross' => 119.00,
                            'total_net' => 100.00,
                        ],
                        [
                            'id' => 2,
                            'client_id' => 456,
                            'status' => InvoiceStatus::PAID->value,
                            'invoice_number' => 'RE-2025-0002',
                            'date' => '2025-01-02',
                            'due_date' => '2025-01-16',
                            'currency_code' => 'EUR',
                            'total_gross' => 59.50,
                            'total_net' => 50.00,
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $filters = ['per_page' => 50];

        $invoices = $api->list($filters);

        self::assertIsArray($invoices);
        self::assertCount(2, $invoices);
        self::assertContainsOnlyInstancesOf(Invoice::class, $invoices);

        $first = $invoices[0];
        self::assertSame(1, $first->id);
        self::assertSame(123, $first->clientId);
        self::assertSame(InvoiceStatus::DRAFT, $first->status);
        self::assertNull($first->invoiceNumber);
        self::assertSame('2025-01-01', $first->date);
        self::assertSame('2025-01-15', $first->dueDate);
        self::assertSame('EUR', $first->currencyCode);
        self::assertSame(119.0, $first->totalGross);
        self::assertSame(100.0, $first->totalNet);

        // Request prüfen
        self::assertSame('GET', $captured['method']);

        $url = $captured['url'];
        $parts = parse_url($url);

        self::assertSame('/api/invoices', $parts['path'] ?? null);

        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        self::assertSame(50, (int)($query['per_page'] ?? 0));
    }

    public function test_it_gets_single_invoice_by_id(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'status' => InvoiceStatus::OPEN->value,
                    'invoice_number' => 'RE-2025-0001',
                    'date' => '2025-02-01',
                    'due_date' => '2025-02-15',
                    'currency_code' => 'EUR',
                    'total_gross' => 238.00,
                    'total_net' => 200.00,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $invoice = $api->get(1234);

        self::assertInstanceOf(Invoice::class, $invoice);
        self::assertSame(1234, $invoice->id);
        self::assertSame(999, $invoice->clientId);
        self::assertSame(InvoiceStatus::OPEN, $invoice->status);
        self::assertSame('RE-2025-0001', $invoice->invoiceNumber);
        self::assertSame('2025-02-01', $invoice->date);
        self::assertSame('2025-02-15', $invoice->dueDate);
        self::assertSame('EUR', $invoice->currencyCode);
        self::assertSame(238.0, $invoice->totalGross);
        self::assertSame(200.0, $invoice->totalNet);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/1234',
            $captured['url']
        );
    }

    public function test_it_creates_a_new_invoice_draft_via_post(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Response einer frisch angelegten Rechnung im Status DRAFT
            $body = json_encode([
                'invoice' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => InvoiceStatus::DRAFT->value,
                    'invoice_number' => null, // laut Doku: bei DRAFT leer
                    'date' => '2025-03-01',
                    'due_date' => '2025-03-15',
                    'currency_code' => 'EUR',
                    'total_gross' => 0.0,
                    'total_net' => 0.0,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        // --- Build invoice create options ---
        $opts = new InvoiceCreateOptions(
            clientId: 123,
        );

        $opts->date = '2025-03-01';
        $opts->currencyCode = 'EUR';
        $opts->title = 'Rechnung März';
        $opts->label = 'Leistungen März 2025';
        $opts->note = 'Vielen Dank für Ihren Auftrag.';

        // --- Add a single invoice item ---
        $item = new InvoiceItemCreateOptions(
            quantity: 2.0,
            unitPrice: 100.0,
        );
        $item->title = 'Beratung';
        $item->description = 'Leistungspaket März';
        $item->unit = 'Stunde';
        $item->taxRate = 19.0;

        $opts->addItem($item);

        // --- Execute ---
        $created = $api->create($opts);

        // --- Assertions on response mapping ---
        self::assertInstanceOf(Invoice::class, $created);
        self::assertSame(777, $created->id);
        self::assertSame(123, $created->clientId);
        self::assertSame(InvoiceStatus::DRAFT, $created->status);
        self::assertNull($created->invoiceNumber);
        self::assertSame('2025-03-01', $created->date);
        self::assertSame('2025-03-15', $created->dueDate);
        self::assertSame('EUR', $created->currencyCode);

        // --- Assertions on outgoing request ---
        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices',
            $captured['url']
        );

        $options = $captured['options'] ?? [];
        $payload = $options['json'] ?? null;

        if ($payload === null && isset($options['body']) && is_string($options['body'])) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('invoice', $payload);

        $invoicePayload = $payload['invoice'];

        // Basic fields
        self::assertSame(123, $invoicePayload['client_id'] ?? null);
        self::assertSame('2025-03-01', $invoicePayload['date'] ?? null);
        self::assertSame('EUR', $invoicePayload['currency_code'] ?? null);
        self::assertSame('Rechnung März', $invoicePayload['title'] ?? null);
        self::assertSame('Leistungen März 2025', $invoicePayload['label'] ?? null);
        self::assertSame('Vielen Dank für Ihren Auftrag.', $invoicePayload['note'] ?? null);

        // Items block
        self::assertArrayHasKey('items', $invoicePayload);
        self::assertArrayHasKey('item', $invoicePayload['items']);

        $items = $invoicePayload['items']['item'];
        self::assertIsArray($items);
        self::assertCount(1, $items);

        $firstItem = $items[0];

        self::assertSame(2.0, $firstItem['quantity'] ?? null);
        self::assertSame(100.0, $firstItem['unit_price'] ?? null);
        self::assertSame('Beratung', $firstItem['title'] ?? null);
        self::assertSame('Leistungspaket März', $firstItem['description'] ?? null);
        self::assertSame('Stunde', $firstItem['unit'] ?? null);
        self::assertSame(19.0, $firstItem['tax_rate'] ?? null);

        // id darf im Payload NICHT gesetzt sein
        self::assertArrayNotHasKey('id', $invoicePayload);
    }

    public function test_it_completes_invoice_via_put_and_optional_template_id(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Billomat verhält sich in echt so: 200 OK mit leerem Body
            return new MockResponse('', ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $templateId = 5;

        $result = $api->complete(777, $templateId);

        // complete() gibt nur true zurück, wenn kein HTTP-Fehler kam
        self::assertTrue($result);

        // Request prüfen
        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/777/complete',
            $captured['url']
        );

        $options = $captured['options'] ?? [];

        // Symfony HttpClient benutzt normalerweise 'json' für JSON-Bodies
        $payload = $options['json'] ?? null;

        // Fallback: falls aus irgendeinem Grund ein roher Body gesetzt wurde
        if ($payload === null && isset($options['body']) && is_string($options['body'])) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('invoice', $payload);
        self::assertSame(
            $templateId,
            $payload['invoice']['template_id'] ?? null
        );
    }
}