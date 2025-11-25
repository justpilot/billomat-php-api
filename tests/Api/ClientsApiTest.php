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
                        ['id' => 1, 'name' => 'Client A'],
                        ['id' => 2, 'name' => 'Client B'],
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

        // Rückgabe prüfen
        self::assertIsArray($clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);
        self::assertSame('Client A', $clients[0]->name);

        // Request prüfen
        self::assertSame('GET', $captured['method']);

        $url = $captured['url'];
        $parts = parse_url($url);

        self::assertSame('/api/clients', $parts['path'] ?? null);

        // Query-Parameter aus der URL auslesen (robust, falls HttpClient sie anhängt)
        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        self::assertSame(50, (int)($query['per_page'] ?? 0));
    }
}