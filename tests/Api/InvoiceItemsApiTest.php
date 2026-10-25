<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\ItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\InvoiceItem;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ItemsApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[UsesClass(InvoicesApi::class)]
#[CoversClass(InvoiceItem::class)]
#[UsesClass(InvoiceItemCreateOptions::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class InvoiceItemsApiTest extends TestCase
{
    #[Test]
    public function itListsItemsByInvoiceAndPassesFilters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
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
                            'total_gross' => '119',
                            'total_net' => '100',
                            'total_gross_unreduced' => '130',
                            'total_net_unreduced' => '110',
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
                            'total_gross' => '214',
                            'total_net' => '200',
                            'total_gross_unreduced' => '214',
                            'total_net_unreduced' => '200',
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

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->items;

        $items = $api->list(123, ['per_page' => 50]);

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
        self::assertSame('50', $first->unitPrice->toString());
        self::assertSame('USt 19%', $first->taxName);
        self::assertSame(19.0, $first->taxRate);
        self::assertFalse($first->taxChangedManually);
        self::assertSame('Position A', $first->title);
        self::assertSame('Beschreibung A', $first->description);
        self::assertSame('10%', $first->reduction);
        self::assertSame(ItemType::SERVICE, $first->type);
        self::assertSame('119', $first->totalGross?->toString());
        self::assertSame('100', $first->totalNet?->toString());
        self::assertSame('130', $first->totalGrossUnreduced?->toString());
        self::assertSame('110', $first->totalNetUnreduced?->toString());

        $second = $items[1];
        self::assertSame(2, $second->id);
        self::assertSame(ItemType::PRODUCT, $second->type);
        self::assertTrue($second->taxChangedManually);

        // Request prüfen
        self::assertSame('GET', $captured['method']);
        self::assertIsString($captured['url']);
        self::assertStringStartsWith(
            'https://mycompany.billomat.net/api/invoice-items',
            $captured['url']
        );
        self::assertStringContainsString('invoice_id=123', $captured['url']);
        self::assertStringContainsString('per_page=50', $captured['url']);
    }

    #[Test]
    public function itGetsSingleItemById(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
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
                    'total_gross' => '267.75',
                    'total_net' => '225',
                    'total_gross_unreduced' => '267.75',
                    'total_net_unreduced' => '225',
                    'created' => '2025-03-02T10:00:00+01:00',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->items;

        $item = $api->get(10);

        self::assertInstanceOf(InvoiceItem::class, $item);
        self::assertSame(10, $item->id);
        self::assertSame(999, $item->invoiceId);
        self::assertSame(ItemType::SERVICE, $item->type);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items/10',
            $captured['url']
        );
    }

    #[Test]
    public function itReturnsNullWhenItemNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->items;

        $item = $api->get(999999);

        self::assertNull($item);
    }

    #[Test]
    public function itCreatesInvoiceItemViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Payload robust ermitteln (json oder body)
            $payload = $options['json'] ?? null;

            if (null === $payload && isset($options['body']) && \is_string($options['body'])) {
                $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
            }

            self::assertIsArray($payload);
            self::assertArrayHasKey('invoice-item', $payload);
            self::assertSame(123, $payload['invoice-item']['invoice_id'] ?? null);
            self::assertSame('Neue Position', $payload['invoice-item']['title'] ?? null);

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
                    'total_gross' => '119',
                    'total_net' => '100',
                    'total_gross_unreduced' => '119',
                    'total_net_unreduced' => '100',
                    'created' => '2025-03-03T10:00:00+01:00',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->items;

        $options = new InvoiceItemCreateOptions(
            quantity: 2.0,
            unitPrice: Money::of('50'),
            unit: 'Stück',
            taxName: 'USt 19%',
            taxRate: 19.0,
            title: 'Neue Position',
            description: 'Aus Tests',
            type: ItemType::SERVICE,
        );

        $created = $api->create(123, $options);

        self::assertSame(77, $created->id);
        self::assertSame(123, $created->invoiceId);
        self::assertSame('Neue Position', $created->title);
        self::assertSame(ItemType::SERVICE, $created->type);

        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items',
            $captured['url']
        );
    }

    #[Test]
    public function itUpdatesInvoiceItemViaPut(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Payload robust ermitteln (json oder body)
            $payload = $options['json'] ?? null;

            if (null === $payload && isset($options['body']) && \is_string($options['body'])) {
                $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
            }

            self::assertIsArray($payload);
            self::assertArrayHasKey('invoice-item', $payload);
            self::assertSame('Geänderter Titel', $payload['invoice-item']['title'] ?? null);

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
                    'total_gross' => '267.75',
                    'total_net' => '225',
                    'total_gross_unreduced' => '281.85',
                    'total_net_unreduced' => '237.5',
                    'created' => '2025-03-03T10:00:00+01:00',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->items;

        $options = new InvoiceItemCreateOptions(
            quantity: 3.0,
            unitPrice: Money::of('75'),
            unit: 'Stück',
            taxName: 'USt 19%',
            taxRate: 19.0,
            taxChangedManually: true,
            title: 'Geänderter Titel',
            description: 'Geänderte Beschreibung',
            reduction: '5%',
            type: ItemType::PRODUCT,
        );

        $updated = $api->update(77, $options);

        self::assertSame(77, $updated->id);
        self::assertSame(ItemType::PRODUCT, $updated->type);
        self::assertTrue($updated->taxChangedManually);
        self::assertSame('Geänderter Titel', $updated->title);
        self::assertSame('5%', $updated->reduction);

        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items/77',
            $captured['url']
        );
    }

    #[Test]
    public function itDeletesInvoiceItemViaDelete(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
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

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->items;

        $api->delete(77);
        self::assertSame('DELETE', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-items/77',
            $captured['url']
        );
    }

    #[Test]
    public function listByInvoiceNormalisesSingleObjectIntoList(): void
    {
        // Billomat liefert bei genau einer Position oft direkt ein Objekt statt einer Liste
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode([
                'invoice-items' => [
                    'invoice-item' => [
                        'id' => 1,
                        'invoice_id' => 99,
                        'quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $api = new InvoicesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig(billomatId: 'x', apiKey: 'k'),
        ))->items;

        $items = $api->list(99);

        self::assertCount(1, $items);
        self::assertSame(1, $items[0]->id);
    }

    #[Test]
    public function listByInvoiceReturnsEmptyListWhenNodeMissing(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['invoice-items' => []], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $api = new InvoicesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig(billomatId: 'x', apiKey: 'k'),
        ))->items;

        self::assertSame([], $api->list(99));
    }
}
