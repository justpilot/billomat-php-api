<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\ConfirmationItem;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ConfirmationItemsApi::class)]
#[CoversClass(ConfirmationItemCreateOptions::class)]
#[CoversClass(ConfirmationItem::class)]
final class ConfirmationItemsApiTest extends TestCase
{
    #[Test]
    public function itListsItemsByConfirmation(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'confirmation-items' => [
                    'confirmation-item' => [
                        ['id' => 1, 'confirmation_id' => 123, 'quantity' => 2, 'unit_price' => 50, 'type' => 'SERVICE'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $items = $api->listByConfirmation(123);

        self::assertCount(1, $items);
        self::assertSame(InvoiceItemType::SERVICE, $items[0]->type);
        self::assertStringContainsString('confirmation_id=123', $captured['url']);
    }

    #[Test]
    public function itCreatesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'confirmation-item' => ['id' => 77, 'confirmation_id' => 123, 'quantity' => 2, 'unit_price' => 50],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ConfirmationItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ConfirmationItemCreateOptions(quantity: 2.0, unitPrice: 50.0);
        $opts->title = 'Neu';

        $created = $api->create(123, $opts);

        self::assertSame(77, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/confirmation-items', $captured['url']);
    }

    #[Test]
    public function itUpdatesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'confirmation-item' => ['id' => 77, 'confirmation_id' => 123, 'quantity' => 3, 'unit_price' => 75],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ConfirmationItemCreateOptions(quantity: 3.0, unitPrice: 75.0);
        self::assertSame(77, $api->update(77, $opts)->id);
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/confirmation-items/77', $captured['url']);
    }

    #[Test]
    public function itDeletesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ConfirmationItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(77));
        self::assertSame('DELETE', $captured['method']);
    }
}
