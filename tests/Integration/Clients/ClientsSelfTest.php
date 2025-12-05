<?php

declare(strict_types=1);

namespace Integration\Clients;

use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

final class ClientsSelfTest extends AbstractBillomatIntegrationTestCase
{
    #[Group("integration")]
    public function test_can_fetch_myself_from_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $me = $billomat->clients->getMyself();

        self::assertInstanceOf(Client::class, $me);
        self::assertNotNull($me->id);
        self::assertGreaterThan(0, $me->id);
        self::assertNotSame('', trim($me->name));
    }
}