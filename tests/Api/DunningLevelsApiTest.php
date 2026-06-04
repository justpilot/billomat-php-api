<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\DunningLevelsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\DunningLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(DunningLevelsApi::class)]
#[CoversClass(DunningLevel::class)]
final class DunningLevelsApiTest extends TestCase
{
    private function http(MockHttpClient $mock): BillomatHttpClient
    {
        return new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret-key'));
    }

    #[Test]
    public function itListsDunningLevels(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertStringContainsString('/api/dunning-levels', $url);

            return new MockResponse(json_encode([
                'dunning-levels' => [
                    'dunning-level' => [
                        ['id' => 1, 'name' => '1. Mahnung', 'position' => 1, 'due_days' => 14, 'charge' => 5.0, 'interest' => 0.0],
                        ['id' => 2, 'name' => '2. Mahnung', 'position' => 2, 'due_days' => 30, 'charge' => 10.0, 'interest' => 4.5],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new DunningLevelsApi($this->http($mock));

        $levels = $api->list();
        self::assertCount(2, $levels);
        self::assertSame('1. Mahnung', $levels[0]->name);
        self::assertSame(14, $levels[0]->dueDays);
        self::assertSame(4.5, $levels[1]->interest);
    }

    #[Test]
    public function itPassesFiltersInQuery(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringContainsString('level=1', $url);

            return new MockResponse(json_encode([
                'dunning-levels' => [
                    'dunning-level' => [],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new DunningLevelsApi($this->http($mock));

        self::assertSame([], $api->list(['level' => 1]));
    }

    #[Test]
    public function listNormalisesSingleObjectIntoList(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'dunning-levels' => [
                'dunning-level' => ['id' => 7, 'name' => 'Solo', 'due_days' => 7],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new DunningLevelsApi($this->http($mock));

        $levels = $api->list();
        self::assertCount(1, $levels);
        self::assertSame(7, $levels[0]->id);
    }

    #[Test]
    public function listReturnsEmptyListWhenNodeMissing(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'dunning-levels' => [],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new DunningLevelsApi($this->http($mock));

        self::assertSame([], $api->list());
    }

    #[Test]
    public function itGetsSingleDunningLevel(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/dunning-levels/2', $url);

            return new MockResponse(json_encode([
                'dunning-level' => ['id' => 2, 'name' => '2. Mahnung', 'position' => 2, 'due_days' => 30],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new DunningLevelsApi($this->http($mock));

        $level = $api->get(2);
        self::assertNotNull($level);
        self::assertSame(2, $level->id);
        self::assertSame(30, $level->dueDays);
    }

    #[Test]
    public function getReturnsNullForUnknown(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('Not Found', ['http_code' => 404]));

        $api = new DunningLevelsApi($this->http($mock));

        self::assertNull($api->get(999));
    }

    #[Test]
    public function getPropagatesAuthError(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('forbidden', ['http_code' => 403]));

        $api = new DunningLevelsApi($this->http($mock));

        $this->expectException(AuthenticationException::class);
        $api->get(1);
    }
}
