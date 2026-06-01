<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringItemsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\RecurringItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(RecurringItemsApi::class)]
#[CoversClass(RecurringItem::class)]
#[CoversClass(RecurringItemCreateOptions::class)]
final class RecurringItemsApiTest extends TestCase
{
    #[Test]
    public function listByRecurringSendsRecurringIdFilter(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'recurring-items' => [
                    'recurring-item' => [
                        ['id' => 1, 'recurring_id' => 42, 'quantity' => 1.0, 'unit_price' => 50.0, 'title' => 'A'],
                        ['id' => 2, 'recurring_id' => 42, 'quantity' => 2.0, 'unit_price' => 25.0, 'title' => 'B'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringItemsApi($http);

        $items = $api->listByRecurring(42);

        self::assertCount(2, $items);
        self::assertContainsOnlyInstancesOf(RecurringItem::class, $items);
        self::assertSame(42, $items[0]->recurringId);

        $parts = parse_url((string) $captured['url']);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('42', $query['recurring_id'] ?? null);
    }

    #[Test]
    public function createPostsItemWithRecurringIdInjected(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'recurring-item' => [
                    'id' => 200,
                    'recurring_id' => 42,
                    'quantity' => 1.5,
                    'unit_price' => 99.0,
                    'title' => 'Setup',
                    'type' => 'SERVICE',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringItemsApi($http);

        $opts = new RecurringItemCreateOptions(quantity: 1.5, unitPrice: 99.0);
        $opts->title = 'Setup';
        $opts->type = InvoiceItemType::SERVICE;

        $item = $api->create(42, $opts);

        self::assertSame(200, $item->id);
        self::assertSame(42, $item->recurringId);
        self::assertSame(InvoiceItemType::SERVICE, $item->type);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertSame(42, $payload['recurring-item']['recurring_id']);
        self::assertSame('Setup', $payload['recurring-item']['title']);
        self::assertSame('SERVICE', $payload['recurring-item']['type']);
    }

    #[Test]
    public function updateSendsPutToItemEndpoint(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'recurring-item' => ['id' => 100, 'recurring_id' => 42, 'quantity' => 2.0, 'unit_price' => 75.0],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringItemsApi($http);

        $opts = new RecurringItemCreateOptions(quantity: 2.0, unitPrice: 75.0);

        $item = $api->update(100, $opts);

        self::assertSame(2.0, $item->quantity);
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/recurring-items/100', $captured['url']);
    }

    #[Test]
    public function deleteSendsDeleteRequest(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringItemsApi($http);

        self::assertTrue($api->delete(100));
        self::assertSame('DELETE', $captured['method']);
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return array<string,mixed>|null
     */
    private function extractJsonPayload(array $options): ?array
    {
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body']) && '' !== $options['body']) {
            $decoded = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);

            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        return \is_array($payload) ? $payload : null;
    }
}
