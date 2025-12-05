<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\TaxesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\TaxRate;
use Justpilot\Billomat\Api\TaxRateCreateOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class TaxesApiTest extends TestCase
{
    public function test_it_lists_tax_rates(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'taxes' => [
                    'tax' => [
                        [
                            'id' => 1,
                            'account_id' => 123,
                            'name' => 'USt 19%',
                            'rate' => 19.0,
                            'is_default' => 1,
                        ],
                        [
                            'id' => 2,
                            'account_id' => 123,
                            'name' => 'USt 7%',
                            'rate' => 7.0,
                            'is_default' => 0,
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new TaxesApi($http);

        $result = $api->list(['page' => 1, 'per_page' => 50]);

        self::assertCount(2, $result);
        self::assertContainsOnlyInstancesOf(TaxRate::class, $result);

        self::assertSame('USt 19%', $result[0]->name);
        self::assertSame(19.0, $result[0]->rate);
        self::assertTrue($result[0]->isDefault);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/taxes?page=1&per_page=50',
            $captured['url']
        );
    }

    public function test_it_gets_single_tax_rate(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'tax' => [
                    'id' => 5,
                    'account_id' => 999,
                    'name' => 'USt 19%',
                    'rate' => 19.0,
                    'is_default' => 1,
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new TaxesApi($http);

        $tax = $api->get(5);

        self::assertInstanceOf(TaxRate::class, $tax);
        self::assertSame(5, $tax->id);
        self::assertSame('USt 19%', $tax->name);
        self::assertSame(19.0, $tax->rate);
        self::assertTrue($tax->isDefault);
    }

    public function test_it_returns_null_when_tax_not_found(): void
    {
        // 404 wird in getJsonOrNull() zu null gemappt
        $mock = new MockHttpClient([
            new MockResponse('Not found', ['http_code' => 404]),
        ]);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new TaxesApi($http);

        $tax = $api->get(999999);

        self::assertNull($tax);
    }

    public function test_it_creates_tax_rate(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'tax' => [
                    'id' => 10,
                    'account_id' => 123,
                    'name' => 'USt 7%',
                    'rate' => 7.0,
                    'is_default' => 0,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new TaxesApi($http);

        $options = new TaxRateCreateOptions(
            name: 'USt 7%',
            rate: 7.0,
            isDefault: false,
        );

        $created = $api->create($options);

        self::assertInstanceOf(TaxRate::class, $created);
        self::assertSame(10, $created->id);
        self::assertSame('USt 7%', $created->name);
        self::assertSame(7.0, $created->rate);
        self::assertFalse($created->isDefault);

        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/taxes',
            $captured['url']
        );

        // Payload ermitteln: bevorzugt "json", Fallback auf "body"
        $optionsArray = $captured['options'] ?? [];
        $payload = $optionsArray['json'] ?? null;

        if ($payload === null && isset($optionsArray['body']) && is_string($optionsArray['body'])) {
            $payload = json_decode($optionsArray['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('tax', $payload);
        self::assertSame('USt 7%', $payload['tax']['name']);
        self::assertSame(7.0, $payload['tax']['rate']);
        self::assertSame(0, $payload['tax']['is_default']);
    }

    public function test_it_updates_tax_rate(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'tax' => [
                    'id' => 10,
                    'account_id' => 123,
                    'name' => 'USt 7% (neu)',
                    'rate' => 7.0,
                    'is_default' => 1,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new TaxesApi($http);

        $options = new TaxRateCreateOptions(
            name: 'USt 7% (neu)',
            rate: 7.0,
            isDefault: true,
        );

        $updated = $api->update(10, $options);

        self::assertInstanceOf(TaxRate::class, $updated);
        self::assertSame(10, $updated->id);
        self::assertSame('USt 7% (neu)', $updated->name);
        self::assertSame(7.0, $updated->rate);
        self::assertTrue($updated->isDefault);

        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/taxes/10',
            $captured['url']
        );
    }

    public function test_it_deletes_tax_rate(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            return new MockResponse('', ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new TaxesApi($http);

        $result = $api->delete(10);

        self::assertTrue($result);
        self::assertSame('DELETE', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/taxes/10',
            $captured['url']
        );
    }
}