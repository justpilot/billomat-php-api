<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\CreditNoteItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CreditNoteItemsApi::class)]
#[CoversClass(CreditNoteItemCreateOptions::class)]
#[CoversClass(CreditNoteItem::class)]
final class CreditNoteItemsApiTest extends TestCase
{
    #[Test]
    public function itListsItemsByCreditNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'credit-note-items' => [
                    'credit-note-item' => [
                        ['id' => 1, 'credit_note_id' => 123, 'quantity' => 2, 'unit_price' => 50, 'title' => 'A'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNoteItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $items = $api->listByCreditNote(123);

        self::assertCount(1, $items);
        self::assertSame(2.0, $items[0]->quantity);
        self::assertStringContainsString('credit_note_id=123', $captured['url']);
    }

    #[Test]
    public function itCreatesUpdatesDeletes(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            return new MockResponse(
                json_encode([
                    'credit-note-item' => ['id' => 77, 'credit_note_id' => 123, 'quantity' => 2, 'unit_price' => 50],
                ], JSON_THROW_ON_ERROR),
                ['http_code' => 200]
            );
        });

        $api = new CreditNoteItemsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new CreditNoteItemCreateOptions(quantity: 2.0, unitPrice: 50.0);
        self::assertSame(77, $api->create(123, $opts)->id);
        self::assertSame(77, $api->update(77, $opts)->id);
        self::assertTrue($api->delete(77));

        self::assertSame('POST', $captured[0]['method']);
        self::assertSame('PUT', $captured[1]['method']);
        self::assertSame('DELETE', $captured[2]['method']);
    }
}
