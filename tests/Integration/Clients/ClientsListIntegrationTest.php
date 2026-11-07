<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ClientsListIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canListClientsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $clients = $billomat->clients->list(['per_page' => 5]);

        self::assertIsArray($clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        if ([] !== $clients) {
            $first = $clients[0];
            self::assertIsString($first->name);
        }
    }

    /**
     * Billomat liest ein unkodiertes "+" als Leerzeichen; nur "%2B" findet den Kunden.
     * In `order_by` dagegen muss das "+" ein Leerzeichen werden ("date+DESC" → "date%20DESC").
     */
    #[Group('integration')]
    #[Test]
    public function filtersByAnEmailAddressContainingAPlusAndSorts(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $email = 'sdk+'.bin2hex(random_bytes(4)).'@example.com';
        $client = $billomat->clients->create(new ClientCreateOptions(name: 'SDK Plus-Test', email: $email));

        try {
            $found = $billomat->clients->list(['email' => $email]);
            self::assertSame([$client->id], array_map(static fn (Client $c): int => $c->id, $found));

            $sorted = $billomat->clients->list(['per_page' => 2, 'order_by' => OrderBy::desc('id')->toQuery()]);
            self::assertSame($client->id, $sorted[0]->id ?? null);
        } finally {
            $billomat->clients->delete($client->id);
        }
    }
}
