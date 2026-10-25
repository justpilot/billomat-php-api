<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

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
}
