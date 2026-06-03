<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ActivitiesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Activity;
use Justpilot\Billomat\Pagination\Page;
use Justpilot\Billomat\Pagination\PageInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;
use const PHP_URL_QUERY;

#[CoversClass(ActivitiesApi::class)]
#[CoversClass(Activity::class)]
#[CoversClass(Page::class)]
#[CoversClass(PageInfo::class)]
final class ActivitiesApiTest extends TestCase
{
    #[Test]
    public function itListsActivitiesAndHandlesEmptyUserAsSystem(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;

            $body = json_encode([
                'activity-feed' => [
                    '@page' => '1',
                    '@per_page' => '100',
                    '@total' => '2',
                    'activity' => [
                        [
                            'resource' => 'invoices',
                            'id' => 835694,
                            'date' => '2014-07-10T10:06:11+02:00',
                            'title' => 'Rechnung RE123',
                            'text' => 'Status geändert von Entwurf nach offen.',
                            'user_id' => 5716,
                        ],
                        [
                            'resource' => 'invoices',
                            'id' => 835697,
                            'date' => '2014-07-10T09:37:50+02:00',
                            'title' => 'Rechnung RE456',
                            'text' => 'Rechnung erstellt.',
                            'user_id' => '',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));

        $activities = new ActivitiesApi($http)->list(['resource' => 'invoices']);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/activity-feed?resource=invoices',
            $captured['url']
        );

        self::assertCount(2, $activities);
        self::assertSame('invoices', $activities[0]->resource);
        self::assertSame(835694, $activities[0]->id);
        self::assertSame(5716, $activities[0]->userId);
        self::assertFalse($activities[0]->isSystemActivity());

        self::assertNull($activities[1]->userId);
        self::assertTrue($activities[1]->isSystemActivity());
    }

    #[Test]
    public function itReturnsEmptyListWhenNoActivities(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['activity-feed' => []], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $activities = new ActivitiesApi($http)->list();

        self::assertSame([], $activities);
    }

    #[Test]
    public function listPageReturnsPaginationMetadata(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode([
                'activity-feed' => [
                    '@page' => '2',
                    '@per_page' => '50',
                    '@total' => '120',
                    'activity' => [
                        [
                            'resource' => 'invoices',
                            'id' => 1,
                            'date' => '2024-01-01T00:00:00+00:00',
                            'title' => 'RE001',
                            'text' => 'x',
                            'user_id' => 1,
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $page = new ActivitiesApi($http)->listPage(['page' => 2, 'per_page' => 50]);

        self::assertSame(2, $page->info->page);
        self::assertSame(50, $page->info->perPage);
        self::assertSame(120, $page->info->total);
        self::assertCount(1, $page->items);
    }

    #[Test]
    public function iterateAllStopsWhenPageReturnsFewerItems(): void
    {
        $calls = 0;
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$calls): MockResponse {
            ++$calls;
            $query = parse_url($url, PHP_URL_QUERY) ?? '';
            parse_str($query, $params);
            $page = (int) ($params['page'] ?? 1);

            $items = match ($page) {
                1 => array_fill(0, 100, ['resource' => 'invoices', 'id' => 1, 'date' => '2024-01-01T00:00:00+00:00', 'title' => 't', 'text' => 't', 'user_id' => 1]),
                2 => [['resource' => 'invoices', 'id' => 2, 'date' => '2024-01-02T00:00:00+00:00', 'title' => 't', 'text' => 't', 'user_id' => 1]],
                default => [],
            };

            return new MockResponse(
                json_encode([
                    'activity-feed' => [
                        '@page' => (string) $page,
                        '@per_page' => '100',
                        'activity' => $items,
                    ],
                ], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            );
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $collected = iterator_to_array(new ActivitiesApi($http)->iterateAll());

        self::assertCount(101, $collected);
        self::assertSame(2, $calls);
    }
}
