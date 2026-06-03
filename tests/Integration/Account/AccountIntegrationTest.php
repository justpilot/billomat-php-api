<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Account;

use Justpilot\Billomat\Model\Account;
use Justpilot\Billomat\Model\AccountQuota;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class AccountIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canFetchOwnAccountFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $account = $billomat->account->get();

        self::assertInstanceOf(Account::class, $account);
        self::assertInstanceOf(Client::class, $account->client);
        self::assertNotNull($account->client->id);
        self::assertNotSame('', $account->client->name);

        foreach ($account->quotas as $quota) {
            self::assertInstanceOf(AccountQuota::class, $quota);
            self::assertNotSame('', $quota->entity);
            self::assertGreaterThanOrEqual(0, $quota->used);
            // -1 markiert „unbegrenzt"; sonst muss available >= 0 sein.
            self::assertTrue(-1 === $quota->available || $quota->available >= 0);
        }
    }
}
