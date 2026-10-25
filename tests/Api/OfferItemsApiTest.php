<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ItemsApi;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\OfferItem;
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
#[UsesClass(OffersApi::class)]
#[CoversClass(OfferItemCreateOptions::class)]
#[CoversClass(OfferItem::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
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

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $items = $api->list(123);

        self::assertCount(1, $items);
        self::assertContainsOnlyInstancesOf(OfferItem::class, $items);
        self::assertSame(ItemType::SERVICE, $items[0]->type);
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

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $item = $api->get(10);

        self::assertInstanceOf(OfferItem::class, $item);
        self::assertSame(10, $item->id);
        self::assertSame(ItemType::PRODUCT, $item->type);
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

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $opts = new OfferItemCreateOptions(quantity: 2.0, unitPrice: Money::of('50'), title: 'Neue Position');

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

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $opts = new OfferItemCreateOptions(quantity: 3.0, unitPrice: Money::of('75'));
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

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $api->delete(77);
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offer-items/77', $captured['url']);
    }
}
