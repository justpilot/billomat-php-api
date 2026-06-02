<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\IncomingPropertyValueCreateOptions;
use Justpilot\Billomat\Api\IncomingPropertyValuesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(IncomingPropertyValuesApi::class)]
#[CoversClass(IncomingPropertyValueCreateOptions::class)]
#[CoversClass(IncomingPropertyValue::class)]
final class IncomingPropertyValuesApiTest extends TestCase
{
    #[Test]
    public function itListsAndCreatesValues(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

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

        $api = new IncomingPropertyValuesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $values = $api->list(['incoming_id' => 42]);
        self::assertCount(1, $values);
        self::assertSame('Büro', $values[0]->value);

        $created = $api->create(new IncomingPropertyValueCreateOptions(
            incomingId: 42,
            incomingPropertyId: 7,
            value: 'IT',
        ));
        self::assertSame(99, $created->id);
    }
}
