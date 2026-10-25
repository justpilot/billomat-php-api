<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\ItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\DeliveryNoteItem;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
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
#[UsesClass(DeliveryNotesApi::class)]
#[CoversClass(DeliveryNoteItemCreateOptions::class)]
#[CoversClass(DeliveryNoteItem::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class DeliveryNoteItemsApiTest extends TestCase
{
    #[Test]
    public function itListsItemsByDeliveryNote(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'delivery-note-items' => [
                    'delivery-note-item' => [
                        ['id' => 1, 'delivery_note_id' => 123, 'quantity' => 5, 'unit_price' => 0, 'title' => 'Pos A'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $items = $api->list(123);

        self::assertCount(1, $items);
        self::assertSame(5.0, $items[0]->quantity);
        self::assertStringContainsString('delivery_note_id=123', $captured->url);
    }

    #[Test]
    public function itCreatesItem(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            $body = json_encode([
                'delivery-note-item' => ['id' => 77, 'delivery_note_id' => 123, 'quantity' => 2, 'unit_price' => 0],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $opts = new DeliveryNoteItemCreateOptions(quantity: 2.0, unitPrice: Money::of('0'), title: 'Neu');

        $created = $api->create(123, $opts);

        self::assertSame(77, $created->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/delivery-note-items', $captured->url);
    }

    #[Test]
    public function itUpdatesAndDeletesItem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = CapturedRequest::of($method, $url);

            if ('PUT' === $method) {
                $body = json_encode([
                    'delivery-note-item' => ['id' => 77, 'delivery_note_id' => 123, 'quantity' => 3, 'unit_price' => 0],
                ], JSON_THROW_ON_ERROR);

                return new MockResponse($body, ['http_code' => 200]);
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $opts = new DeliveryNoteItemCreateOptions(quantity: 3.0, unitPrice: Money::of('0'));
        self::assertSame(77, $api->update(77, $opts)->id);
        $api->delete(77);

        self::assertSame('PUT', $captured[0]->method);
        self::assertSame('DELETE', $captured[1]->method);
    }
}
