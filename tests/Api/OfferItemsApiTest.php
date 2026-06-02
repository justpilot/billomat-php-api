<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\OfferItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(OfferItemsApi::class)]
#[CoversClass(OfferItemCreateOptions::class)]
#[CoversClass(OfferItem::class)]
final class OfferItemsApiTest extends TestCase
{
    #[Test]
    public function itListsItemsByOffer(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'offer-items' => [
                    'offer-item' => [
                        [
                            'id' => 1,
                            'offer_id' => 123,
                            'quantity' => 2,
                            'unit_price' => 50,
                            'type' => 'SERVICE',
                            'title' => 'Position A',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OfferItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $items = $api->listByOffer(123);

        self::assertCount(1, $items);
        self::assertContainsOnlyInstancesOf(OfferItem::class, $items);
        self::assertSame(InvoiceItemType::SERVICE, $items[0]->type);
        self::assertStringContainsString('offer_id=123', $captured['url']);
    }

    #[Test]
    public function itGetsSingleItem(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'offer-item' => [
                    'id' => 10,
                    'offer_id' => 999,
                    'quantity' => 3,
                    'unit_price' => 75,
                    'type' => 'PRODUCT',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OfferItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $item = $api->get(10);

        self::assertInstanceOf(OfferItem::class, $item);
        self::assertSame(10, $item->id);
        self::assertSame(InvoiceItemType::PRODUCT, $item->type);
    }

    #[Test]
    public function itCreatesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'offer-item' => [
                    'id' => 77,
                    'offer_id' => 123,
                    'quantity' => 2,
                    'unit_price' => 50,
                    'title' => 'Neue Position',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new OfferItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new OfferItemCreateOptions(quantity: 2.0, unitPrice: 50.0);
        $opts->title = 'Neue Position';

        $created = $api->create(123, $opts);

        self::assertSame(77, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offer-items', $captured['url']);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertSame(123, $payload['offer-item']['offer_id']);
        self::assertSame('Neue Position', $payload['offer-item']['title']);
    }

    #[Test]
    public function itUpdatesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'offer-item' => [
                    'id' => 77,
                    'offer_id' => 123,
                    'quantity' => 3,
                    'unit_price' => 75,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OfferItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new OfferItemCreateOptions(quantity: 3.0, unitPrice: 75.0);
        $updated = $api->update(77, $opts);

        self::assertSame(77, $updated->id);
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offer-items/77', $captured['url']);
    }

    #[Test]
    public function itDeletesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new OfferItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(77));
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offer-items/77', $captured['url']);
    }
}
