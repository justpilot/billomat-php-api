<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValuesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\SupplierPropertyValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(SupplierPropertyValuesApi::class)]
#[CoversClass(SupplierPropertyValueCreateOptions::class)]
#[CoversClass(SupplierPropertyValue::class)]
final class SupplierPropertyValuesApiTest extends TestCase
{
    #[Test]
    public function itListsValuesBySupplier(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'supplier-property-values' => [
                    'supplier-property-value' => [
                        ['id' => 1, 'supplier_id' => 42, 'supplier_property_id' => 7, 'name' => 'Branche', 'value' => 'IT'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new SupplierPropertyValuesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $values = $api->list(['supplier_id' => 42]);

        self::assertCount(1, $values);
        self::assertSame('Branche', $values[0]->name);
        self::assertSame('IT', $values[0]->value);
        self::assertStringContainsString('supplier_id=42', $captured['url']);
    }

    #[Test]
    public function itCreatesValue(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'supplier-property-value' => ['id' => 99, 'supplier_id' => 42, 'supplier_property_id' => 7, 'value' => 'Logistik'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new SupplierPropertyValuesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $value = $api->create(new SupplierPropertyValueCreateOptions(
            supplierId: 42,
            supplierPropertyId: 7,
            value: 'Logistik',
        ));

        self::assertSame(99, $value->id);
        self::assertSame('POST', $captured['method']);
    }
}
