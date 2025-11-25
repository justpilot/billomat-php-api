<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\TestCase;

final class BillomatClientIntegrationTest extends TestCase
{
    /**
     * @group integration
     */
    public function test_can_list_clients_from_sandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID') ?: null;
        $apiKey = getenv('BILLOMAT_API_KEY') ?: null;

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('BILLOMAT_ID or BILLOMAT_API_KEY not set in .env.test/.env.test.local');
        }

        $client = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        // Wir versuchen, max. 1 Client zu holen – egal ob es überhaupt Clients gibt
        $clients = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($clients, 'Clients result must be an array');
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        if ($clients !== []) {
            $first = $clients[0];

            self::assertInstanceOf(Client::class, $first);
            self::assertIsInt($first->id);
            self::assertIsString($first->name);
            self::assertNotSame('', $first->name);
        }
    }
}