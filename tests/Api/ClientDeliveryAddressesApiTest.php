<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ClientDeliveryAddressCreateOptions;
use Justpilot\Billomat\Api\ClientDeliveryAddressesApi;
use Justpilot\Billomat\Api\ClientDeliveryAddressUpdateOptions;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\SubResourceFilter;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ClientDeliveryAddress;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ClientDeliveryAddressesApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[CoversClass(ClientDeliveryAddress::class)]
#[CoversClass(ClientDeliveryAddressCreateOptions::class)]
#[CoversClass(ClientDeliveryAddressUpdateOptions::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(BillomatClient::class)]
#[UsesClass(ClientsApi::class)]
#[UsesClass(OrderBy::class)]
#[UsesClass(SubResourceFilter::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ClientDeliveryAddressesApiTest extends TestCase
{
    #[Test]
    public function itIsReachableAsSubResourceOfClientsAndListsByClient(): void
    {
        $captured = new CapturedRequest();
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse(json_encode([
                'client-delivery-addresses' => [
                    '@page' => '1',
                    '@per_page' => '100',
                    '@total' => '1',
                    'client-delivery-address' => [
                        'id' => '28',
                        'client_id' => '42',
                        'name' => 'Lager Nord',
                        'street' => 'Hafenstraße 1',
                        'zip' => '20457',
                        'city' => 'Hamburg',
                        'country_code' => 'DE',
                        'is_default' => '1',
                    ],
                ],
            ], JSON_THROW_ON_ERROR));
        });
        $billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'k', httpClient: new Psr18Client($mock));

        // Lazy erzeugt und danach wiederverwendet.
        self::assertSame($billomat->clients->deliveryAddresses, $billomat->clients->deliveryAddresses);

        $addresses = $billomat->clients->deliveryAddresses->list(42, new SubResourceFilter(orderBy: OrderBy::asc('name')));

        self::assertSame('GET', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses?client_id=42&order_by=name%20ASC', $captured->url);
        // Einzelner Treffer kommt als Objekt statt als Liste.
        self::assertCount(1, $addresses);
        self::assertSame(28, $addresses[0]->id);
        self::assertSame(42, $addresses[0]->clientId);
        self::assertSame('Lager Nord', $addresses[0]->name);
        self::assertSame('DE', $addresses[0]->countryCode);
        self::assertTrue($addresses[0]->isDefault);
    }

    #[Test]
    public function listWithoutClientIdListsTheAddressesOfAllClients(): void
    {
        $captured = new CapturedRequest();
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('{"client-delivery-addresses":{"@page":"1","@per_page":"100","@total":"0"}}');
        });

        self::assertSame([], $this->api($mock)->list());
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses', $captured->url);
    }

    #[Test]
    public function createSendsTheClientIdFromTheArgument(): void
    {
        $captured = new CapturedRequest();
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('{"client-delivery-address":{"id":"5","client_id":"42","name":"Lager Nord"}}', ['http_code' => 201]);
        });

        $address = $this->api($mock)->create(42, new ClientDeliveryAddressCreateOptions(
            name: 'Lager Nord',
            addressLine2: 'Tor 3',
            countryCode: 'DE',
        ));

        self::assertSame(5, $address->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses', $captured->url);
        self::assertSame(
            ['client-delivery-address' => ['name' => 'Lager Nord', 'address_line_2' => 'Tor 3', 'country_code' => 'DE', 'client_id' => 42]],
            $captured->json(),
        );
    }

    #[Test]
    public function updateSendsOnlyTheGivenFields(): void
    {
        $captured = new CapturedRequest();
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('{"client-delivery-address":{"id":"5","client_id":"42","city":"Bremen"}}');
        });

        $address = $this->api($mock)->update(5, new ClientDeliveryAddressUpdateOptions(city: 'Bremen', isDefault: true));

        self::assertSame('Bremen', $address->city);
        self::assertSame('PUT', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses/5', $captured->url);
        self::assertSame(['client-delivery-address' => ['city' => 'Bremen', 'is_default' => true]], $captured->json());
    }

    #[Test]
    public function getReturnsNullForMissingAddressAndDeleteSendsDelete(): void
    {
        $captured = new CapturedRequest();
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
            static function (string $method, string $url, array $options) use ($captured): MockResponse {
                $captured->record($method, $url, $options);

                return new MockResponse('', ['http_code' => 200]);
            },
        ]);
        $api = $this->api($mock);

        self::assertNull($api->get(999));

        $api->delete(5);
        self::assertSame('DELETE', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses/5', $captured->url);
    }

    private function api(MockHttpClient $mock): ClientDeliveryAddressesApi
    {
        return new ClientDeliveryAddressesApi(new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig(billomatId: 'mycompany', apiKey: 'k')));
    }
}
