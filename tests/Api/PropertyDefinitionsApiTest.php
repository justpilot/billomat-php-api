<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ClientPropertiesApi;
use Justpilot\Billomat\Api\IncomingPropertiesApi;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertiesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\ClientProperty;
use Justpilot\Billomat\Model\Enum\PropertyType;
use Justpilot\Billomat\Model\IncomingProperty;
use Justpilot\Billomat\Model\SupplierProperty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ClientPropertiesApi::class)]
#[CoversClass(IncomingPropertiesApi::class)]
#[CoversClass(SupplierPropertiesApi::class)]
#[CoversClass(PropertyCreateOptions::class)]
#[CoversClass(ClientProperty::class)]
#[CoversClass(IncomingProperty::class)]
#[CoversClass(SupplierProperty::class)]
final class PropertyDefinitionsApiTest extends TestCase
{
    private function http(MockHttpClient $mock): BillomatHttpClient
    {
        return new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret-key'));
    }

    // ---------------------- ClientPropertiesApi ----------------------

    #[Test]
    public function itListsClientProperties(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertStringContainsString('/api/client-properties', $url);

            return new MockResponse(json_encode([
                'client-properties' => [
                    'client-property' => [
                        ['id' => 1, 'name' => 'VAT-ID', 'type' => 'TEXTFIELD', 'position' => 1],
                        ['id' => 2, 'name' => 'Kundengruppe', 'type' => 'TEXTAREA', 'position' => 2],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new ClientPropertiesApi($this->http($mock));

        $props = $api->list();
        self::assertCount(2, $props);
        self::assertSame('VAT-ID', $props[0]->name);
        self::assertSame(PropertyType::TEXTFIELD, $props[0]->type);
        self::assertSame(2, $props[1]->position);
    }

    #[Test]
    public function clientPropertiesListNormalisesSingleObject(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'client-properties' => [
                'client-property' => ['id' => 7, 'name' => 'Nur eine', 'type' => 'TEXTFIELD'],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new ClientPropertiesApi($this->http($mock));

        $props = $api->list();
        self::assertCount(1, $props);
        self::assertSame(7, $props[0]->id);
    }

    #[Test]
    public function clientPropertiesListReturnsEmptyArrayWhenNodeMissing(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'client-properties' => [],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new ClientPropertiesApi($this->http($mock));

        self::assertSame([], $api->list());
    }

    #[Test]
    public function itGetsSingleClientProperty(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/client-properties/42', $url);

            return new MockResponse(json_encode([
                'client-property' => ['id' => 42, 'name' => 'Premium-Status', 'type' => 'CHECKBOX'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new ClientPropertiesApi($this->http($mock));

        $prop = $api->get(42);
        self::assertNotNull($prop);
        self::assertSame(42, $prop->id);
        self::assertSame(PropertyType::CHECKBOX, $prop->type);
    }

    #[Test]
    public function getReturnsNullForUnknownClientProperty(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('Not Found', ['http_code' => 404]));

        $api = new ClientPropertiesApi($this->http($mock));

        self::assertNull($api->get(999));
    }

    #[Test]
    public function itCreatesClientProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'body' => $options['body'] ?? null];

            return new MockResponse(json_encode([
                'client-property' => ['id' => 100, 'name' => 'Neues Feld', 'type' => 'TEXTAREA'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 201]);
        });

        $api = new ClientPropertiesApi($this->http($mock));

        $opts = new PropertyCreateOptions(name: 'Neues Feld');
        $opts->type = PropertyType::TEXTAREA;
        $opts->position = 5;

        $created = $api->create($opts);

        self::assertSame(100, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertStringEndsWith('/api/client-properties', $captured['url']);
        self::assertIsString($captured['body']);
        $payload = json_decode($captured['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Neues Feld', $payload['client-property']['name']);
        self::assertSame('TEXTAREA', $payload['client-property']['type']);
        self::assertSame(5, $payload['client-property']['position']);
    }

    #[Test]
    public function itUpdatesClientProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse(json_encode([
                'client-property' => ['id' => 42, 'name' => 'Geändert', 'type' => 'TEXTFIELD'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new ClientPropertiesApi($this->http($mock));

        $opts = new PropertyCreateOptions(name: 'Geändert');
        $opts->type = PropertyType::TEXTFIELD;

        $updated = $api->update(42, $opts);

        self::assertSame('Geändert', $updated->name);
        self::assertSame('PUT', $captured['method']);
        self::assertStringEndsWith('/api/client-properties/42', $captured['url']);
    }

    #[Test]
    public function itDeletesClientProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ClientPropertiesApi($this->http($mock));

        self::assertTrue($api->delete(42));
        self::assertSame('DELETE', $captured['method']);
        self::assertStringEndsWith('/api/client-properties/42', $captured['url']);
    }

    // ---------------------- IncomingPropertiesApi ----------------------

    #[Test]
    public function itListsIncomingProperties(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringContainsString('/api/incoming-properties', $url);

            return new MockResponse(json_encode([
                'incoming-properties' => [
                    'incoming-property' => [
                        ['id' => 1, 'name' => 'Kostenstelle', 'type' => 'TEXTFIELD'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new IncomingPropertiesApi($this->http($mock));

        $props = $api->list();
        self::assertCount(1, $props);
        self::assertSame('Kostenstelle', $props[0]->name);
    }

    #[Test]
    public function itCreatesIncomingProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'body' => $options['body'] ?? null];

            return new MockResponse(json_encode([
                'incoming-property' => ['id' => 11, 'name' => 'Projekt', 'type' => 'CHECKBOX'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 201]);
        });

        $api = new IncomingPropertiesApi($this->http($mock));

        $opts = new PropertyCreateOptions(name: 'Projekt');
        $opts->type = PropertyType::CHECKBOX;

        $created = $api->create($opts);

        self::assertSame(11, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertStringEndsWith('/api/incoming-properties', $captured['url']);
        self::assertIsString($captured['body']);
        $payload = json_decode($captured['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('CHECKBOX', $payload['incoming-property']['type']);
    }

    #[Test]
    public function itGetsSingleIncomingProperty(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/incoming-properties/3', $url);

            return new MockResponse(json_encode([
                'incoming-property' => ['id' => 3, 'name' => 'Steuer-Klasse', 'type' => 'TEXTFIELD'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new IncomingPropertiesApi($this->http($mock));

        $prop = $api->get(3);
        self::assertNotNull($prop);
        self::assertSame('Steuer-Klasse', $prop->name);
    }

    #[Test]
    public function itDeletesIncomingProperty(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('', ['http_code' => 200]));

        $api = new IncomingPropertiesApi($this->http($mock));

        self::assertTrue($api->delete(3));
    }

    // ---------------------- SupplierPropertiesApi ----------------------

    #[Test]
    public function itListsSupplierProperties(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringContainsString('/api/supplier-properties', $url);

            return new MockResponse(json_encode([
                'supplier-properties' => [
                    'supplier-property' => [
                        ['id' => 1, 'name' => 'Zahlungsziel', 'type' => 'TEXTFIELD'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new SupplierPropertiesApi($this->http($mock));

        $props = $api->list();
        self::assertCount(1, $props);
        self::assertSame('Zahlungsziel', $props[0]->name);
    }

    #[Test]
    public function supplierPropertiesListNormalisesSingleObject(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'supplier-properties' => [
                'supplier-property' => ['id' => 9, 'name' => 'Bonität', 'type' => 'TEXTFIELD'],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new SupplierPropertiesApi($this->http($mock));

        $props = $api->list();
        self::assertCount(1, $props);
        self::assertSame(9, $props[0]->id);
    }

    #[Test]
    public function supplierPropertiesListReturnsEmptyArrayWhenNodeMissing(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'supplier-properties' => [],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new SupplierPropertiesApi($this->http($mock));

        self::assertSame([], $api->list());
    }

    #[Test]
    public function itCreatesSupplierProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'body' => $options['body'] ?? null];

            return new MockResponse(json_encode([
                'supplier-property' => ['id' => 21, 'name' => 'IBAN-Country', 'type' => 'TEXTFIELD'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 201]);
        });

        $api = new SupplierPropertiesApi($this->http($mock));

        $opts = new PropertyCreateOptions(name: 'IBAN-Country');
        $opts->type = PropertyType::TEXTFIELD;

        $created = $api->create($opts);

        self::assertSame(21, $created->id);
        self::assertStringEndsWith('/api/supplier-properties', $captured['url']);
        self::assertIsString($captured['body']);
        $payload = json_decode($captured['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('IBAN-Country', $payload['supplier-property']['name']);
    }

    #[Test]
    public function itUpdatesSupplierProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse(json_encode([
                'supplier-property' => ['id' => 21, 'name' => 'Geändert', 'type' => 'TEXTFIELD'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new SupplierPropertiesApi($this->http($mock));

        $opts = new PropertyCreateOptions(name: 'Geändert');

        $updated = $api->update(21, $opts);

        self::assertSame('Geändert', $updated->name);
        self::assertSame('PUT', $captured['method']);
        self::assertStringEndsWith('/api/supplier-properties/21', $captured['url']);
    }

    #[Test]
    public function itGetsSingleSupplierProperty(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/supplier-properties/5', $url);

            return new MockResponse(json_encode([
                'supplier-property' => ['id' => 5, 'name' => 'Hauptlieferant', 'type' => 'CHECKBOX'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new SupplierPropertiesApi($this->http($mock));

        $prop = $api->get(5);
        self::assertNotNull($prop);
        self::assertSame(PropertyType::CHECKBOX, $prop->type);
    }

    #[Test]
    public function getReturnsNullForUnknownSupplierProperty(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('Not Found', ['http_code' => 404]));

        $api = new SupplierPropertiesApi($this->http($mock));

        self::assertNull($api->get(999));
    }

    #[Test]
    public function itDeletesSupplierProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new SupplierPropertiesApi($this->http($mock));

        self::assertTrue($api->delete(5));
        self::assertSame('DELETE', $captured['method']);
        self::assertStringEndsWith('/api/supplier-properties/5', $captured['url']);
    }
}
