<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\Api\RecurringTagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\RecurringTag;
use Justpilot\Billomat\Model\RecurringTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(RecurringTagsApi::class)]
#[CoversClass(RecurringTag::class)]
#[CoversClass(RecurringTagCloudEntry::class)]
#[CoversClass(RecurringTagCreateOptions::class)]
final class RecurringTagsApiTest extends TestCase
{
    #[Test]
    public function listByRecurringSendsRecurringIdFilter(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['url' => $url];

            $body = json_encode([
                'recurring-tags' => [
                    'recurring-tag' => [
                        ['id' => 1, 'recurring_id' => 42, 'name' => 'a'],
                        ['id' => 2, 'recurring_id' => 42, 'name' => 'b'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringTagsApi($http);

        $tags = $api->listByRecurring(42);

        self::assertCount(2, $tags);
        self::assertContainsOnlyInstancesOf(RecurringTag::class, $tags);

        $parts = parse_url((string) $captured['url']);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('42', $query['recurring_id'] ?? null);
    }

    #[Test]
    public function cloudReturnsAggregatedEntries(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'recurring-tags' => [
                    'tag' => [
                        ['id' => 1, 'name' => 'foo', 'count' => 4],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringTagsApi($http);

        $cloud = $api->cloud();

        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(RecurringTagCloudEntry::class, $cloud);
        self::assertSame(4, $cloud[0]->count);
    }

    #[Test]
    public function createAndDeleteWork(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'recurring-tag' => ['id' => 17, 'recurring_id' => 42, 'name' => 'wichtig'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 201]),
            new MockResponse('', ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringTagsApi($http);

        $tag = $api->create(new RecurringTagCreateOptions(recurringId: 42, name: 'wichtig'));
        self::assertSame(17, $tag->id);

        self::assertTrue($api->delete(17));
    }

    #[Test]
    public function getReturnsNullOnNotFound(): void
    {
        $mock = new MockHttpClient([new MockResponse('', ['http_code' => 404])]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringTagsApi($http);

        self::assertNull($api->get(999));
    }
}
