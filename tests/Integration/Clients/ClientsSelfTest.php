<?php

declare(strict_types=1);

namespace Integration\Clients;

use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ClientsSelfTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canFetchMyselfFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $me = $billomat->clients->getMyself();

        self::assertNotNull($me->id);
        self::assertGreaterThan(0, $me->id);
        self::assertNotSame('', trim($me->name));
    }
}
