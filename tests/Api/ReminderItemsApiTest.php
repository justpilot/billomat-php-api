<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ReminderItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ReminderItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ReminderItemsApi::class)]
#[CoversClass(ReminderItem::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ReminderItemsApiTest extends TestCase
{
    #[Test]
    public function itListsItemsByReminder(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'reminder-items' => [
                    'reminder-item' => [
                        ['id' => 1, 'reminder_id' => 42, 'quantity' => 1, 'unit_price' => 100, 'title' => 'Verzug RE-1'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ReminderItemsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $items = $api->listByReminder(42);

        self::assertCount(1, $items);
        self::assertSame(100.0, $items[0]->unitPrice);
        self::assertStringContainsString('reminder_id=42', $captured['url']);
    }

    #[Test]
    public function itGetsSingleItem(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'reminder-item' => ['id' => 10, 'reminder_id' => 42, 'quantity' => 1, 'unit_price' => 50],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ReminderItemsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $item = $api->get(10);

        self::assertInstanceOf(ReminderItem::class, $item);
        self::assertSame(10, $item->id);
    }
}
