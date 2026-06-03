<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\SearchApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\SearchResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;
use const PHP_URL_QUERY;

#[CoversClass(SearchApi::class)]
#[CoversClass(SearchResult::class)]
final class SearchApiTest extends TestCase
{
    #[Test]
    public function itQueriesAcrossResources(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;

            return new MockResponse(json_encode([
                'search' => [
                    'result' => [
                        ['resource' => 'reminders', 'id' => 52082, 'headline' => '[14709-003]', 'subline' => '04.08.2014 Hans Wurst GmbH'],
                        ['resource' => 'delivery-notes', 'id' => 24761, 'headline' => '[LS2014_1]', 'subline' => '31.07.2014 ACME Testfirma'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $hits = new SearchApi($http)->query('Hans Wurst');

        self::assertSame('GET', $captured['method']);
        parse_str(parse_url($captured['url'], PHP_URL_QUERY) ?? '', $params);
        self::assertSame('Hans Wurst', $params['query']);

        self::assertCount(2, $hits);
        self::assertSame('reminders', $hits[0]->resource);
        self::assertSame(52082, $hits[0]->id);
        self::assertSame('[14709-003]', $hits[0]->headline);
        self::assertSame('04.08.2014 Hans Wurst GmbH', $hits[0]->subline);

        self::assertSame('delivery-notes', $hits[1]->resource);
    }

    #[Test]
    public function itReturnsEmptyListWhenNoMatches(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['search' => ['@total' => '0']], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));

        self::assertSame([], new SearchApi($http)->query('nichts'));
    }

    #[Test]
    public function extraFiltersAreSentAlongQuery(): void
    {
        $capturedUrl = '';

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$capturedUrl): MockResponse {
            $capturedUrl = $url;

            return new MockResponse(json_encode(['search' => ['result' => []]], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        new SearchApi($http)->query('foo', ['per_page' => 5]);

        parse_str(parse_url($capturedUrl, PHP_URL_QUERY) ?? '', $params);
        self::assertSame('foo', $params['query']);
        self::assertSame('5', $params['per_page']);
    }
}
