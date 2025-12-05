<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BillomatClientTest extends TestCase
{
    public function test_it_wires_clients_api_and_uses_config(): void
    {
        $responses = [
            new MockResponse(json_encode([
                'clients' => [
                    'client' => [
                        ['id' => 1, 'name' => 'Client A'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ];

        $mockHttp = new MockHttpClient($responses);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $client = new BillomatClient($config, $mockHttp);

        $result = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(Client::class, $result);

        $first = $result[0];
        self::assertSame('Client A', $first->name);
        self::assertSame(1, $first->id);
    }

    public function test_static_create_helper_builds_config_and_wires_apis(): void
    {
        $mockHttp = new MockHttpClient([
            new MockResponse(json_encode([
                'clients' => [
                    'client' => [],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $client = BillomatClient::create(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            httpClient: $mockHttp,
        );

        // Smoke-Test: Clients-API funktioniert und liefert ein Array von Client-Objekten
        $clients = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        // zusätzliche Sicherstellung: weitere APIs sind verdrahtet
        self::assertNotNull($client->invoices);
        self::assertNotNull($client->taxes);
    }
}