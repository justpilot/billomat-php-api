<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringsApi;
use Justpilot\Billomat\Api\RecurringUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Recurring;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(RecurringsApi::class)]
#[CoversClass(RecurringCreateOptions::class)]
#[CoversClass(RecurringUpdateOptions::class)]
#[CoversClass(Recurring::class)]
#[CoversClass(RecurringAction::class)]
#[CoversClass(RecurringCycle::class)]
final class RecurringsApiTest extends TestCase
{
    #[Test]
    public function itListsRecurringsAndPassesFilters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'recurrings' => [
                    'recurring' => [
                        [
                            'id' => 1,
                            'client_id' => 5,
                            'name' => 'Hosting',
                            'cycle' => 'MONTHLY',
                            'cycle_number' => 1,
                            'action' => 'EMAIL',
                        ],
                        [
                            'id' => 2,
                            'client_id' => 6,
                            'name' => 'Wartung',
                            'cycle' => 'YEARLY',
                            'cycle_number' => 1,
                            'action' => 'COMPLETE',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringsApi($http);

        $recurrings = $api->list(['client_id' => 5, 'cycle' => 'MONTHLY']);

        self::assertCount(2, $recurrings);
        self::assertContainsOnlyInstancesOf(Recurring::class, $recurrings);
        self::assertSame('Hosting', $recurrings[0]->name);
        self::assertSame(RecurringCycle::MONTHLY, $recurrings[0]->cycle);
        self::assertSame(RecurringAction::EMAIL, $recurrings[0]->action);

        $parts = parse_url((string) $captured['url']);
        self::assertSame('/api/recurrings', $parts['path'] ?? null);
    }

    #[Test]
    public function listNormalizesSingleEntry(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'recurrings' => [
                    'recurring' => ['id' => 1, 'client_id' => 5, 'cycle' => 'MONTHLY'],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringsApi($http);

        $recurrings = $api->list();

        self::assertCount(1, $recurrings);
    }

    #[Test]
    public function getReturnsNullOnNotFound(): void
    {
        $mock = new MockHttpClient([new MockResponse('', ['http_code' => 404])]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringsApi($http);

        self::assertNull($api->get(999));
    }

    #[Test]
    public function getHydratesEmbeddedItemsAndTaxes(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'recurring' => [
                    'id' => 42,
                    'client_id' => 5,
                    'name' => 'Hosting',
                    'cycle' => 'MONTHLY',
                    'cycle_number' => 1,
                    'action' => 'EMAIL',
                    'start_date' => '2026-01-01',
                    'next_creation_date' => '2026-07-01',
                    'taxes' => [
                        'tax' => [
                            ['name' => 'MwSt', 'rate' => 19.0, 'amount' => 19.0],
                        ],
                    ],
                    'recurring-items' => [
                        'recurring-item' => [
                            ['id' => 100, 'recurring_id' => 42, 'quantity' => 1.0, 'unit_price' => 100.0, 'title' => 'Hosting Basic'],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringsApi($http);

        $recurring = $api->get(42);

        self::assertNotNull($recurring);
        self::assertSame(42, $recurring->id);
        self::assertSame('Hosting', $recurring->name);
        self::assertSame('2026-01-01', $recurring->startDate?->format('Y-m-d'));
        self::assertSame('2026-07-01', $recurring->nextCreationDate?->format('Y-m-d'));
        self::assertCount(1, $recurring->taxes);
        self::assertCount(1, $recurring->items);
        self::assertSame(100, $recurring->items[0]->id);
    }

    #[Test]
    public function createPostsRecurringWithEmbeddedItemsAndHydratesResponse(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'recurring' => [
                    'id' => 99,
                    'client_id' => 5,
                    'name' => 'Hosting',
                    'cycle' => 'MONTHLY',
                    'cycle_number' => 1,
                    'action' => 'EMAIL',
                    'start_date' => '2026-07-01',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringsApi($http);

        $opts = new RecurringCreateOptions(clientId: 5);
        $opts->name = 'Hosting';
        $opts->cycle = RecurringCycle::MONTHLY;
        $opts->cycleNumber = 1;
        $opts->action = RecurringAction::EMAIL;
        $opts->startDate = new DateTimeImmutable('2026-07-01');
        $opts->addItem(new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 100.0));

        $recurring = $api->create($opts);

        self::assertSame(99, $recurring->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/recurrings', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        $inner = $payload['recurring'];
        self::assertSame(5, $inner['client_id']);
        self::assertSame('Hosting', $inner['name']);
        self::assertSame('MONTHLY', $inner['cycle']);
        self::assertSame('EMAIL', $inner['action']);
        self::assertSame('2026-07-01', $inner['start_date']);
        self::assertCount(1, $inner['recurring-items']['recurring-item']);
        self::assertSame(1.0, $inner['recurring-items']['recurring-item'][0]['quantity']);
    }

    #[Test]
    public function updateSendsPutAndRetainsClearingItemsFromPayload(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'recurring' => [
                    'id' => 42,
                    'client_id' => 5,
                    'name' => 'Hosting Updated',
                    'cycle' => 'MONTHLY',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringsApi($http);

        $opts = new RecurringUpdateOptions();
        $opts->name = 'Hosting Updated';

        $recurring = $api->update(42, $opts);

        self::assertSame('Hosting Updated', $recurring->name);
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/recurrings/42', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertArrayNotHasKey('recurring-items', $payload['recurring']);
        self::assertSame('Hosting Updated', $payload['recurring']['name']);
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
        $api = new RecurringsApi($http);

        self::assertTrue($api->delete(99));
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/recurrings/99', $captured['url']);
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
