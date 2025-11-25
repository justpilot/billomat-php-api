<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
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

        self::assertCount(1, $result);
        self::assertSame('Client A', $result[0]['name']);
    }

    public function test_static_create_helper_builds_config(): void
    {
        $mockHttp = new MockHttpClient([
            new MockResponse('{"ok": true}'),
        ]);

        $client = BillomatClient::create(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            httpClient: $mockHttp,
        );

        // nur ein Smoke-Test: clients-API existiert
        $this->assertNotNull($client->clients);
    }
}