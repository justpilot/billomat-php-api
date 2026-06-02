<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SuppliersApi;
use Justpilot\Billomat\Api\SupplierUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Supplier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(SuppliersApi::class)]
#[CoversClass(SupplierCreateOptions::class)]
#[CoversClass(SupplierUpdateOptions::class)]
#[CoversClass(Supplier::class)]
final class SuppliersApiTest extends TestCase
{
    #[Test]
    public function itListsSuppliers(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'suppliers' => [
                    'supplier' => [
                        ['id' => 1, 'name' => 'Lieferant A', 'country_code' => 'DE'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $suppliers = $api->list();

        self::assertCount(1, $suppliers);
        self::assertSame('Lieferant A', $suppliers[0]->name);
    }

    #[Test]
    public function itGetsSingleSupplier(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'supplier' => [
                    'id' => 1234,
                    'name' => 'ACME GmbH',
                    'email' => 'info@acme.example',
                    'country_code' => 'DE',
                    'vat_number' => 'DE123456789',
                    'bank_iban' => 'DE89370400440532013000',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $supplier = $api->get(1234);

        self::assertInstanceOf(Supplier::class, $supplier);
        self::assertSame('ACME GmbH', $supplier->name);
        self::assertSame('DE123456789', $supplier->vatNumber);
        self::assertSame('DE89370400440532013000', $supplier->bankIban);
    }

    #[Test]
    public function itCreatesSupplier(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'supplier' => ['id' => 777, 'name' => 'Neu', 'country_code' => 'DE'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new SupplierCreateOptions(name: 'Neu');
        $opts->countryCode = 'DE';
        $opts->email = 'kontakt@example.com';

        $created = $api->create($opts);

        self::assertSame(777, $created->id);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame('Neu', $payload['supplier']['name']);
        self::assertSame('DE', $payload['supplier']['country_code']);
        self::assertSame('kontakt@example.com', $payload['supplier']['email']);
    }

    #[Test]
    public function itUpdatesSupplier(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'supplier' => ['id' => 777, 'name' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new SupplierUpdateOptions();
        $opts->name = 'Geändert';

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->name);
        self::assertSame('PUT', $captured['method']);
    }

    #[Test]
    public function itDeletesSupplier(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(777));
        self::assertSame('DELETE', $captured['method']);
    }
}
