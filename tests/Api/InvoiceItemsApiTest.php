<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\InvoiceItem;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class InvoiceItemsApiTest extends TestCase
{
    public function test_it_lists_items_by_invoice_and_passes_filters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice-items' => [
                    'invoice-item' => [
                        [
                            'id' => 1,
                            'invoice_id' => 123,
                            'article_id' => 42,
                            'position' => 1,
                            'unit' => 'Stück',
                            'quantity' => 2,
                            'unit_price' => 50,
                            'tax_name' => 'USt 19%',
                            'tax_rate' => 19,
                            'tax_changed_manually' => 0,
                            'title' => 'Position A',
                            'description' => 'Beschreibung A',
                            'reduction' => '10%',
                            'type' => 'SERVICE',
                            'total_gross' => 119.0,
                            'total_net' => 100.0,
                            'total_gross_unreduced' => 130.0,
                            'total_net_unreduced' => 110.0,
                            'created' => '2025-03-01T12:00:00+01:00',
                        ],
                        [
                            'id' => 2,
                            'invoice_id' => 123,
                            'article_id' => 43,
                            'position' => 2,
                            'unit' => 'Stück',
                            'quantity' => 1,
                            'unit_price' => 200,
                            'tax_name' => 'USt 7%',
                            'tax_rate' => 7,
                            'tax_changed_manually' => 1,
                            'title' => 'Position B',
                            'description' => 'Beschreibung B',
                            'reduction' => null,
                            'type' => 'PRODUCT',
                            'total_gross' => 214.0,
                            'total_net' => 200.0,
                            'total_gross_unreduced' => 214.0,
                            'total_net_unreduced' => 200.0,
                            'created' => '2025-03-01T13:00:00+01:00',
                        ],
                    ],
                    '@page' => '1',
                    '@per_page' => '50',
                    '@total' => '2',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoiceItemsApi($http);

        $items = $api->listByInvoice(123, ['per_page' => 50]);

        // Rückgabe prüfen
        self::assertIsArray($items);
        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(InvoiceItem::class, $items);

        $first = $items[0];
        self::assertSame(1, $first->id);
        self::assertSame(123, $first->invoiceId);
        self::assertSame(42, $first->articleId);
        self::assertSame(1, $first->position);
        self::assertSame('Stück', $first->unit);
        self::assertSame(2.0, $first->quantity);
        self::assertSame(50.0, $first->unitPrice);
        self::assertSame('USt 19%', $first->taxName);
        self::assertSame(19.0, $first->taxRate);
        self::assertFalse($first->taxChangedManually);
        self::assertSame('Position A', $first->title);
        self::assertSame('Beschreibung A', $first->description);
        self::assertSame('10%', $first->reduction);
        self::assertSame(InvoiceItemType::SERVICE, $first->type);
        self::assertSame(119.0, $first->totalGross);
        self::assertSame(100.0, $first->totalNet);
        self::assertSame(130.0, $first->totalGrossUnreduced);
        self::assertSame(110.0, $first->totalNetUnreduced);
        self::assertInstanceOf(\DateTimeImmutable::class, $first->created);

        $second = $items[1];
        self::assertSame(2, $second->id);
        self::assertSame(InvoiceItemType::PRODUCT, $second->type);
        self::assertTrue($second->taxChangedManually);

        // Request prüfen
        self::assertSame('GET', $captured['method']);
        self::assertStringStartsWith(
            'https://mycompany.billomat.net/api/invoice-items',
            $captured['url']
        );
        self::assertStringContainsString('invoice_id=123', $captured['url']);
        self::assertStringContainsString('per_page=50', $captured['url']);
    }

    public function test_it_gets_single_item_by_id(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice-item' => [
                    'id' => 10,
                    'invoice_id' => 999,
                    'article_id' => 55,
                    'position' => 1,
                    'unit' => 'Stück',
                    'quantity' => 3,
                    'unit_price' => 75,
                    'tax_name' => 'USt 19%',
                    'tax_rate' => 19,
                    'tax_changed_manually' => 0,
                    'title' => 'Einzel-Item',
                    'description' => 'Ein Item',
                    'reduction' => null,
                    'type' => 'SERVICE',
                    'total_gross' => 267.75,
                    'total_net' => 225.0,
                    'total_gross_unreduced' => 267.75,
                    'total_net_unreduced' => 225.0,
                    'created' => '2025-03-02T10:00:00+01:00',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoiceItemsApi($http);

        $item = $api->get(10);

        self::assertInstanceOf(InvoiceItem::class, $item);
        self::assertSame(10, $item->id);
        self::assertSame(999, $item->invoiceId);
        self::assertSame(InvoiceItemType::SERVICE, $item->type);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items/10',
            $captured['url']
        );
    }

    public function test_it_returns_null_when_item_not_found(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoiceItemsApi($http);

        $item = $api->get(999999);

        self::assertNull($item);
    }

    public function test_it_creates_invoice_item_via_post(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Payload robust ermitteln (json oder body)
            $payload = $options['json'] ?? null;

            if ($payload === null && isset($options['body']) && is_string($options['body'])) {
                $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
            }

            $this->assertIsArray($payload);
            $this->assertArrayHasKey('invoice-item', $payload);
            $this->assertSame(123, $payload['invoice-item']['invoice_id'] ?? null);
            $this->assertSame('Neue Position', $payload['invoice-item']['title'] ?? null);

            $body = json_encode([
                'invoice-item' => [
                    'id' => 77,
                    'invoice_id' => 123,
                    'article_id' => null,
                    'position' => 1,
                    'unit' => 'Stück',
                    'quantity' => 2,
                    'unit_price' => 50,
                    'tax_name' => 'USt 19%',
                    'tax_rate' => 19,
                    'tax_changed_manually' => 0,
                    'title' => 'Neue Position',
                    'description' => 'Aus Tests',
                    'reduction' => null,
                    'type' => 'SERVICE',
                    'total_gross' => 119.0,
                    'total_net' => 100.0,
                    'total_gross_unreduced' => 119.0,
                    'total_net_unreduced' => 100.0,
                    'created' => '2025-03-03T10:00:00+01:00',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoiceItemsApi($http);

        $options = new InvoiceItemCreateOptions(
            quantity: 2.0,
            unitPrice: 50.0,
        );
        $options->title = 'Neue Position';
        $options->description = 'Aus Tests';
        $options->unit = 'Stück';
        $options->taxName = 'USt 19%';
        $options->taxRate = 19.0;
        $options->type = InvoiceItemType::SERVICE;

        $created = $api->create(123, $options);

        self::assertInstanceOf(InvoiceItem::class, $created);
        self::assertSame(77, $created->id);
        self::assertSame(123, $created->invoiceId);
        self::assertSame('Neue Position', $created->title);
        self::assertSame(InvoiceItemType::SERVICE, $created->type);

        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items',
            $captured['url']
        );
    }

    public function test_it_updates_invoice_item_via_put(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Payload robust ermitteln (json oder body)
            $payload = $options['json'] ?? null;

            if ($payload === null && isset($options['body']) && is_string($options['body'])) {
                $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
            }

            $this->assertIsArray($payload);
            $this->assertArrayHasKey('invoice-item', $payload);
            $this->assertSame('Geänderter Titel', $payload['invoice-item']['title'] ?? null);

            $body = json_encode([
                'invoice-item' => [
                    'id' => 77,
                    'invoice_id' => 123,
                    'article_id' => null,
                    'position' => 1,
                    'unit' => 'Stück',
                    'quantity' => 3,
                    'unit_price' => 75,
                    'tax_name' => 'USt 19%',
                    'tax_rate' => 19,
                    'tax_changed_manually' => 1,
                    'title' => 'Geänderter Titel',
                    'description' => 'Geänderte Beschreibung',
                    'reduction' => '5%',
                    'type' => 'PRODUCT',
                    'total_gross' => 267.75,
                    'total_net' => 225.0,
                    'total_gross_unreduced' => 281.85,
                    'total_net_unreduced' => 237.5,
                    'created' => '2025-03-03T10:00:00+01:00',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoiceItemsApi($http);

        $options = new InvoiceItemCreateOptions(
            quantity: 3.0,
            unitPrice: 75.0,
        );
        $options->title = 'Geänderter Titel';
        $options->description = 'Geänderte Beschreibung';
        $options->unit = 'Stück';
        $options->taxName = 'USt 19%';
        $options->taxRate = 19.0;
        $options->taxChangedManually = true;
        $options->reduction = '5%';
        $options->type = InvoiceItemType::PRODUCT;

        $updated = $api->update(77, $options);

        self::assertInstanceOf(InvoiceItem::class, $updated);
        self::assertSame(77, $updated->id);
        self::assertSame(InvoiceItemType::PRODUCT, $updated->type);
        self::assertTrue($updated->taxChangedManually);
        self::assertSame('Geänderter Titel', $updated->title);
        self::assertSame('5%', $updated->reduction);

        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items/77',
            $captured['url']
        );
    }

    public function test_it_deletes_invoice_item_via_delete(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // 200 OK, leerer Body
            return new MockResponse('', ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoiceItemsApi($http);

        $result = $api->delete(77);

        self::assertTrue($result);
        self::assertSame('DELETE', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items/77',
            $captured['url']
        );
    }
}