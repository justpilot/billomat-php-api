<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\UnitsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(UnitsApi::class)]
#[CoversClass(Unit::class)]
final class UnitsApiTest extends TestCase
{
    private function http(MockHttpClient $mock): BillomatHttpClient
    {
        return new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret-key'));
    }

    #[Test]
    public function itListsUnits(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertStringContainsString('/api/units', $url);

            return new MockResponse(json_encode([
                'units' => [
                    'unit' => [
                        ['id' => 1, 'name' => 'Stück'],
                        ['id' => 2, 'name' => 'Stunde'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new UnitsApi($this->http($mock));

        $units = $api->list();
        self::assertCount(2, $units);
        self::assertSame('Stück', $units[0]->name);
        self::assertSame(2, $units[1]->id);
    }

    #[Test]
    public function listNormalisesSingleObjectIntoList(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'units' => [
                'unit' => ['id' => 5, 'name' => 'Tag'],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new UnitsApi($this->http($mock));

        $units = $api->list();
        self::assertCount(1, $units);
        self::assertSame('Tag', $units[0]->name);
    }

    #[Test]
    public function listReturnsEmptyListWhenNodeMissing(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'units' => [],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new UnitsApi($this->http($mock));

        self::assertSame([], $api->list());
    }

    #[Test]
    public function itGetsSingleUnit(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/units/3', $url);

            return new MockResponse(json_encode([
                'unit' => ['id' => 3, 'name' => 'Kilogramm'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new UnitsApi($this->http($mock));

        $unit = $api->get(3);
        self::assertNotNull($unit);
        self::assertSame('Kilogramm', $unit->name);
    }

    #[Test]
    public function getReturnsNullForUnknownUnit(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('Not Found', ['http_code' => 404]));

        $api = new UnitsApi($this->http($mock));

        self::assertNull($api->get(999));
    }
}
