<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientDeliveryAddressCreateOptions;
use Justpilot\Billomat\Api\ClientDeliveryAddressesApi;
use Justpilot\Billomat\Api\ClientDeliveryAddressUpdateOptions;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\SubResourceFilter;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ClientDeliveryAddress;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(ClientDeliveryAddressesApi::class)]
#[CoversClass(ClientDeliveryAddressCreateOptions::class)]
#[CoversClass(ClientDeliveryAddressUpdateOptions::class)]
#[CoversClass(ClientDeliveryAddress::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(BillomatClient::class)]
#[UsesClass(ClientsApi::class)]
#[UsesClass(OrderBy::class)]
#[UsesClass(SubResourceFilter::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesNamespace('Justpilot\Billomat\Exception')]
final class ClientDeliveryAddressesApiTest extends TestCase
{
    /**
     * Antwort von GET /client-delivery-addresses/2 aus der Sandbox (2026-11-22): alle Werte als Strings.
     */
    private const string SANDBOX_ADDRESS = '{"id":"2","created":"2026-11-22T20:35:22+01:00","updated":"2026-11-22T20:35:22+01:00","client_id":"10651661","label":"SDK-Beispiel Lager","name":"SDK-Beispiel GmbH","salutation":"Frau","first_name":"Erika","last_name":"Musterfrau","street":"Lagerweg 5","address_line_2":"Tor 3","address_line_3":"Rampe B","zip":"10115","city":"Berlin","state":"Berlin","country_code":"DE","is_default":"0"}';

    #[Test]
    public function itListsTheAddressesOfAClient(): void
    {
        $captured = new CapturedRequest();
        $api = $this->api($captured, '{"client-delivery-addresses":{"client-delivery-address":['.self::SANDBOX_ADDRESS.'],"@page":"1","@per_page":"100","@total":"1"}}');

        $addresses = $api->list(10651661);

        self::assertCount(1, $addresses);
        self::assertSame('SDK-Beispiel Lager', $addresses[0]->label);
        self::assertSame('GET', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses?client_id=10651661', $captured->url);
    }

    #[Test]
    public function aSingleAddressInTheListIsNormalized(): void
    {
        $api = $this->api(new CapturedRequest(), '{"client-delivery-addresses":{"client-delivery-address":'.self::SANDBOX_ADDRESS.',"@total":"1"}}');

        $addresses = $api->list(10651661);

        self::assertCount(1, $addresses);
        self::assertSame(2, $addresses[0]->id);
    }

    #[Test]
    public function theParentIdWinsOverAFilterOfTheSameName(): void
    {
        $captured = new CapturedRequest();
        $api = $this->api($captured, '{"client-delivery-addresses":{"@page":"2","@per_page":"5","@total":"0"}}');

        self::assertSame([], $api->list(42, new SubResourceFilter(orderBy: OrderBy::desc('created'), page: 2, perPage: 5)));
        self::assertSame(['client_id' => '42', 'order_by' => 'created DESC', 'page' => '2', 'per_page' => '5'], $captured->query());

        (void) $api->listPage(42, ['client_id' => 7]);
        self::assertSame(['client_id' => '42'], $captured->query());
    }

    #[Test]
    public function itIsReachableAsSubResourceOfClients(): void
    {
        $captured = new CapturedRequest();
        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            return new MockResponse('{"client-delivery-addresses":{"@total":"0"}}');
        });
        $billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'k', httpClient: new Psr18Client($mock));

        // Lazy erzeugt und danach wiederverwendet.
        self::assertSame($billomat->clients->deliveryAddresses, $billomat->clients->deliveryAddresses);

        self::assertSame([], $billomat->clients->deliveryAddresses->list(42));
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses?client_id=42', $captured->url);
    }

    #[Test]
    public function itHydratesTheSandboxResponse(): void
    {
        $captured = new CapturedRequest();
        $api = $this->api($captured, '{"client-delivery-address":'.self::SANDBOX_ADDRESS.'}');

        $address = $api->getOrFail(2);

        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses/2', $captured->url);
        self::assertSame(2, $address->id);
        self::assertSame(10651661, $address->clientId);
        self::assertEquals(new DateTimeImmutable('2026-11-22T20:35:22+01:00'), $address->created);
        self::assertSame('SDK-Beispiel GmbH', $address->name);
        self::assertSame('Frau', $address->salutation);
        self::assertSame('Erika', $address->firstName);
        self::assertSame('Musterfrau', $address->lastName);
        self::assertSame('Lagerweg 5', $address->street);
        self::assertSame('Tor 3', $address->addressLine2);
        self::assertSame('Rampe B', $address->addressLine3);
        self::assertSame('10115', $address->zip);
        self::assertSame('Berlin', $address->city);
        self::assertSame('DE', $address->countryCode);
        self::assertFalse($address->isDefault);
        self::assertSame([], $address->extra);
    }

    #[Test]
    public function getReturnsNullAndGetOrFailThrowsForAMissingAddress(): void
    {
        $api = $this->api(new CapturedRequest(), '{"errors":{"error":"item not found"}}', 404);

        self::assertNull($api->get(3));

        $this->expectException(NotFoundException::class);
        (void) $api->getOrFail(3);
    }

    #[Test]
    public function itCreatesAnAddressForTheGivenClient(): void
    {
        $captured = new CapturedRequest();
        $api = $this->api($captured, '{"client-delivery-address":'.self::SANDBOX_ADDRESS.'}', 201);

        $created = $api->create(10651661, new ClientDeliveryAddressCreateOptions(
            label: 'SDK-Beispiel Lager',
            street: 'Lagerweg 5',
            addressLine2: 'Tor 3',
            countryCode: 'DE',
            isDefault: true,
        ));

        self::assertSame(2, $created->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses', $captured->url);
        self::assertSame(['client-delivery-address' => [
            'label' => 'SDK-Beispiel Lager',
            'street' => 'Lagerweg 5',
            'address_line_2' => 'Tor 3',
            'country_code' => 'DE',
            'is_default' => 1,
            'client_id' => 10651661,
        ]], $captured->json());
    }

    #[Test]
    public function anUpdateOnlySendsTheGivenFields(): void
    {
        $captured = new CapturedRequest();
        $api = $this->api($captured, '{"client-delivery-address":'.self::SANDBOX_ADDRESS.'}');

        $api->update(2, new ClientDeliveryAddressUpdateOptions(city: 'Bremen', isDefault: false));

        self::assertSame('PUT', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses/2', $captured->url);
        self::assertSame(['client-delivery-address' => ['city' => 'Bremen', 'is_default' => 0]], $captured->json());
    }

    #[Test]
    public function itDeletesAnAddress(): void
    {
        $captured = new CapturedRequest();
        $api = $this->api($captured, '');

        $api->delete(2);

        self::assertSame('DELETE', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/client-delivery-addresses/2', $captured->url);
    }

    #[Test]
    public function billomatErrorsBecomeValidationExceptions(): void
    {
        $api = $this->api(new CapturedRequest(), '{"errors":{"error":"no valid country_code given"}}', 400);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIsOrContains('no valid country_code given');

        $api->create(42, new ClientDeliveryAddressCreateOptions(countryCode: 'XX'));
    }

    private function api(CapturedRequest $captured, string $body, int $status = 200): ClientDeliveryAddressesApi
    {
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured, $body, $status): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse($body, ['http_code' => $status]);
        });

        return new ClientDeliveryAddressesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key', retry: new RetryPolicy(maxRetries: 0)),
        ));
    }
}
