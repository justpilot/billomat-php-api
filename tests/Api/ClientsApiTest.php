<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ClientsApiTest extends TestCase
{
    public function test_it_lists_clients_and_passes_filters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'clients' => [
                    'client' => [
                        [
                            'id' => 1,
                            'name' => 'Client A GmbH',
                            'client_number' => 'C001',
                            'street' => 'Musterstraße 1',
                            'zip' => '12345',
                            'city' => 'Berlin',
                            'country_code' => 'DE',
                            'first_name' => 'Max',
                            'last_name' => 'Mustermann',
                            'email' => 'max@example.com',
                            'debitor_account_number' => 4711,
                        ],
                        [
                            'id' => 2,
                            'name' => 'Client B AG',
                            'client_number' => 'C002',
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
        $api = new ClientsApi($http);

        $filters = ['per_page' => 50];

        $clients = $api->list($filters);

        self::assertIsArray($clients);
        self::assertCount(2, $clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        $first = $clients[0];
        self::assertSame(1, $first->id);
        self::assertSame('Client A GmbH', $first->name);
        self::assertSame('C001', $first->clientNumber);
        self::assertSame('Musterstraße 1', $first->street);
        self::assertSame('12345', $first->zip);
        self::assertSame('Berlin', $first->city);
        self::assertSame('DE', $first->countryCode);
        self::assertSame('Max', $first->firstName);
        self::assertSame('Mustermann', $first->lastName);
        self::assertSame('max@example.com', $first->email);
        self::assertSame(4711, $first->debitorAccountNumber);

        // Request prüfen
        self::assertSame('GET', $captured['method']);

        $url = $captured['url'];
        $parts = parse_url($url);

        self::assertSame('/api/clients', $parts['path'] ?? null);

        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        self::assertSame(50, (int)($query['per_page'] ?? 0));
    }

    public function test_it_gets_single_client_by_id(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'client' => [
                    'id' => 123,
                    'name' => 'Single Client GmbH',
                    'client_number' => 'SC-123',
                    'first_name' => 'Erika',
                    'last_name' => 'Musterfrau',
                    'email' => 'erika@example.com',
                    'debitor_account_number' => 9001,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new ClientsApi($http);

        $client = $api->get(123);

        self::assertInstanceOf(Client::class, $client);
        self::assertSame(123, $client->id);
        self::assertSame('Single Client GmbH', $client->name);
        self::assertSame('SC-123', $client->clientNumber);
        self::assertSame('Erika', $client->firstName);
        self::assertSame('Musterfrau', $client->lastName);
        self::assertSame('erika@example.com', $client->email);
        self::assertSame(9001, $client->debitorAccountNumber);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients/123',
            $captured['url']
        );
    }

    public function test_it_creates_a_new_client_via_post(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Server-Response mit gesetzter ID und zurückgespiegelten Daten
            $body = json_encode([
                'client' => [
                    'id' => 999,
                    'name' => 'New Client GmbH',
                    'client_number' => 'C-100',
                    'first_name' => 'Max',
                    'last_name' => 'Mustermann',
                    'salutation' => 'Herr',
                    'email' => 'new@example.com',
                    'country_code' => 'DE',
                    'debitor_account_number' => 4711,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new ClientsApi($http);

        $opts = new ClientCreateOptions();
        $opts->name = 'New Client GmbH';
        $opts->firstName = 'Max';
        $opts->lastName = 'Mustermann';
        $opts->salutation = 'Herr';
        $opts->clientNumber = 'C-100';
        $opts->email = 'new@example.com';
        $opts->countryCode = 'DE';
        $opts->debitorAccountNumber = 4711;

        $created = $api->create($opts);

        self::assertInstanceOf(Client::class, $created);
        self::assertSame(999, $created->id);
        self::assertSame('New Client GmbH', $created->name);
        self::assertSame('C-100', $created->clientNumber);
        self::assertSame('Max', $created->firstName);
        self::assertSame('Mustermann', $created->lastName);
        self::assertSame('Herr', $created->salutation);
        self::assertSame('new@example.com', $created->email);
        self::assertSame('DE', $created->countryCode);
        self::assertSame(4711, $created->debitorAccountNumber);

        // Request prüfen
        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients',
            $captured['url']
        );

        $options = $captured['options'] ?? [];
        $payload = $options['json'] ?? null;

        if ($payload === null && isset($options['body']) && is_string($options['body'])) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('client', $payload);

        $clientPayload = $payload['client'];

        self::assertSame('New Client GmbH', $clientPayload['name'] ?? null);
        self::assertSame('Max', $clientPayload['first_name'] ?? null);
        self::assertSame('Mustermann', $clientPayload['last_name'] ?? null);
        self::assertSame('Herr', $clientPayload['salutation'] ?? null);
        self::assertSame('C-100', $clientPayload['client_number'] ?? null);
        self::assertSame('new@example.com', $clientPayload['email'] ?? null);
        self::assertSame('DE', $clientPayload['country_code'] ?? null);
        self::assertSame(4711, $clientPayload['debitor_account_number'] ?? null);

        // id darf im Payload nicht gesetzt sein
        self::assertArrayNotHasKey('id', $clientPayload);
    }
}