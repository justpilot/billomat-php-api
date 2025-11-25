<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

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
                            'name' => 'Client A',
                            'first_name' => 'Max',
                            'last_name' => 'Mustermann',
                            'salutation' => 'Herr',
                            'client_number' => 'C001',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Client B',
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
        self::assertSame('Client A', $first->name);
        self::assertSame('Max', $first->firstName);
        self::assertSame('Mustermann', $first->lastName);
        self::assertSame('Herr', $first->salutation);
        self::assertSame('C001', $first->clientNumber);

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
                    'name' => 'Single Client',
                    'first_name' => 'Erika',
                    'last_name' => 'Musterfrau',
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
        self::assertSame('Single Client', $client->name);
        self::assertSame('Erika', $client->firstName);
        self::assertSame('Musterfrau', $client->lastName);

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

            // Server-Response mit gesetzter ID
            $body = json_encode([
                'client' => [
                    'id' => 999,
                    'name' => 'New Client GmbH',
                    'first_name' => 'Max',
                    'last_name' => 'Mustermann',
                    'salutation' => 'Herr',
                    'client_number' => 'C-100',
                    'email' => 'new@example.com',
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

        $newClient = Client::new(
            name: 'New Client GmbH',
            firstName: 'Max',
            lastName: 'Mustermann',
            salutation: 'Herr',
            clientNumber: 'C-100',
            email: 'new@example.com',
        );

        $created = $api->create($newClient);

        self::assertSame(999, $created->id);
        self::assertSame('New Client GmbH', $created->name);
        self::assertSame('Max', $created->firstName);
        self::assertSame('Mustermann', $created->lastName);
        self::assertSame('Herr', $created->salutation);
        self::assertSame('C-100', $created->clientNumber);
        self::assertSame('new@example.com', $created->email);

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
        self::assertSame('New Client GmbH', $payload['client']['name'] ?? null);
        self::assertSame('Max', $payload['client']['first_name'] ?? null);
        self::assertSame('Mustermann', $payload['client']['last_name'] ?? null);
        self::assertSame('Herr', $payload['client']['salutation'] ?? null);
        self::assertSame('C-100', $payload['client']['client_number'] ?? null);
        self::assertSame('new@example.com', $payload['client']['email'] ?? null);
        self::assertArrayNotHasKey('id', $payload['client']);
    }
}