<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class ClientsListIntegrationTest extends TestCase
{
    #[Group('integration')]
    #[Test]
    public function canListClientsFromSandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID');
        $apiKey = getenv('BILLOMAT_API_KEY');

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
        }

        $billomat = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $clients = $billomat->clients->list(['per_page' => 5]);

        self::assertIsArray($clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        if ([] !== $clients) {
            $first = $clients[0];
            self::assertNotNull($first->id);
            self::assertIsString($first->name);
        }
    }
}
