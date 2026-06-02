<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\DeliveryNoteItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(DeliveryNoteItemsApi::class)]
#[CoversClass(DeliveryNoteItemCreateOptions::class)]
#[CoversClass(DeliveryNoteItem::class)]
final class DeliveryNoteItemsApiTest extends TestCase
{
    #[Test]
    public function itListsItemsByDeliveryNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'delivery-note-items' => [
                    'delivery-note-item' => [
                        ['id' => 1, 'delivery_note_id' => 123, 'quantity' => 5, 'unit_price' => 0, 'title' => 'Pos A'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNoteItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $items = $api->listByDeliveryNote(123);

        self::assertCount(1, $items);
        self::assertSame(5.0, $items[0]->quantity);
        self::assertStringContainsString('delivery_note_id=123', $captured['url']);
    }

    #[Test]
    public function itCreatesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'delivery-note-item' => ['id' => 77, 'delivery_note_id' => 123, 'quantity' => 2, 'unit_price' => 0],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new DeliveryNoteItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new DeliveryNoteItemCreateOptions(quantity: 2.0, unitPrice: 0.0);
        $opts->title = 'Neu';

        $created = $api->create(123, $opts);

        self::assertSame(77, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-note-items', $captured['url']);
    }

    #[Test]
    public function itUpdatesAndDeletesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            if ('PUT' === $method) {
                $body = json_encode([
                    'delivery-note-item' => ['id' => 77, 'delivery_note_id' => 123, 'quantity' => 3, 'unit_price' => 0],
                ], JSON_THROW_ON_ERROR);

                return new MockResponse($body, ['http_code' => 200]);
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new DeliveryNoteItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new DeliveryNoteItemCreateOptions(quantity: 3.0, unitPrice: 0.0);
        self::assertSame(77, $api->update(77, $opts)->id);
        self::assertTrue($api->delete(77));

        self::assertSame('PUT', $captured[0]['method']);
        self::assertSame('DELETE', $captured[1]['method']);
    }
}
