<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\ItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\CreditNoteItem;
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
#[UsesClass(CreditNotesApi::class)]
#[CoversClass(CreditNoteItemCreateOptions::class)]
#[CoversClass(CreditNoteItem::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
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

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $items = $api->list(123);

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

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->items;

        $opts = new CreditNoteItemCreateOptions(quantity: 2.0, unitPrice: Money::of('50'));
        self::assertSame(77, $api->create(123, $opts)->id);
        self::assertSame(77, $api->update(77, $opts)->id);
        $api->delete(77);

        self::assertSame('POST', $captured[0]['method']);
        self::assertSame('PUT', $captured[1]['method']);
        self::assertSame('DELETE', $captured[2]['method']);
    }
}
