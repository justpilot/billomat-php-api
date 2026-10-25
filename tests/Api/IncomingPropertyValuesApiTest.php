<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\IncomingPropertyValueCreateOptions;
use Justpilot\Billomat\Api\IncomingsApi;
use Justpilot\Billomat\Api\PropertyValuesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(PropertyValuesApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[UsesClass(IncomingsApi::class)]
#[CoversClass(IncomingPropertyValueCreateOptions::class)]
#[CoversClass(IncomingPropertyValue::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class IncomingPropertyValuesApiTest extends TestCase
{
    #[Test]
    public function itListsAndCreatesValues(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = CapturedRequest::of($method, $url);

            if ('GET' === $method) {
                return new MockResponse(
                    json_encode([
                        'incoming-property-values' => [
                            'incoming-property-value' => [
                                ['id' => 1, 'incoming_id' => 42, 'incoming_property_id' => 7, 'name' => 'Kostenstelle', 'value' => 'Büro'],
                            ],
                        ],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 200]
                );
            }

            return new MockResponse(
                json_encode([
                    'incoming-property-value' => ['id' => 99, 'incoming_id' => 42, 'incoming_property_id' => 7, 'value' => 'IT'],
                ], JSON_THROW_ON_ERROR),
                ['http_code' => 201]
            );
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->propertyValues;

        $values = $api->list(filters: ['incoming_id' => 42]);
        self::assertCount(1, $values);
        self::assertSame('Büro', $values[0]->value);

        $created = $api->create(42, new IncomingPropertyValueCreateOptions(
            incomingPropertyId: 7,
            value: 'IT',
        ));
        self::assertSame(99, $created->id);
    }
}
