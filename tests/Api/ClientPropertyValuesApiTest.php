<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;
use Justpilot\Billomat\Api\ClientPropertyValuesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ClientPropertyValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ClientPropertyValuesApi::class)]
#[CoversClass(ClientPropertyValueCreateOptions::class)]
#[CoversClass(ClientPropertyValue::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ClientPropertyValuesApiTest extends TestCase
{
    #[Test]
    public function itListsValuesByClient(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'client-property-values' => [
                    'client-property-value' => [
                        ['id' => 1, 'client_id' => 42, 'client_property_id' => 7, 'name' => 'Branche', 'value' => 'IT'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ClientPropertyValuesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $values = $api->list(['client_id' => 42]);

        self::assertCount(1, $values);
        self::assertSame('Branche', $values[0]->name);
        self::assertSame('IT', $values[0]->value);
        self::assertStringContainsString('client_id=42', $captured['url']);
    }

    #[Test]
    public function itCreatesValue(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'client-property-value' => ['id' => 99, 'client_id' => 42, 'client_property_id' => 7, 'value' => 'Logistik'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ClientPropertyValuesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $value = $api->create(new ClientPropertyValueCreateOptions(
            clientId: 42,
            clientPropertyId: 7,
            value: 'Logistik',
        ));

        self::assertSame(99, $value->id);
        self::assertSame('POST', $captured['method']);
    }

    #[Test]
    public function itGetsValueAndReturnsNullOn404(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            if (str_ends_with($url, '/api/client-property-values/5')) {
                return new MockResponse(json_encode([
                    'client-property-value' => ['id' => '5', 'client_id' => '42', 'client_property_id' => '7', 'type' => 'CHECKBOX', 'value' => '1'],
                ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
            }

            return new MockResponse('{"errors":{"error":"resource not found"}}', ['http_code' => 404]);
        });

        $api = new ClientPropertyValuesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $value = $api->get(5);
        self::assertNotNull($value);
        self::assertSame(42, $value->clientId);
        self::assertSame(7, $value->clientPropertyId);
        self::assertSame('CHECKBOX', $value->type);

        self::assertNull($api->get(6));
    }
}
