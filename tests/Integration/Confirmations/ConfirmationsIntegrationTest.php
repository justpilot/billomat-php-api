<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Confirmations;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ConfirmationsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureClientId(): int
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] !== $clients) {
            return $clients[0]->id;
        }

        $faker = $this->faker();

        $opts = new ClientCreateOptions();
        $opts->name = $faker->company();
        $opts->email = $faker->unique()->safeEmail();
        $opts->countryCode = 'DE';

        $created = $billomat->clients->create($opts);

        self::assertNotNull($created->id);

        return $created->id;
    }

    #[Group('integration')]
    #[Test]
    public function canListConfirmationsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $confirmations = $billomat->confirmations->list(['per_page' => 5]);

        self::assertIsArray($confirmations);
        self::assertContainsOnlyInstancesOf(Confirmation::class, $confirmations);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateAndDeleteConfirmationDraftInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();
        $faker = $this->faker();

        $opts = new ConfirmationCreateOptions(clientId: $clientId);
        $opts->date = new DateTimeImmutable('today');
        $opts->currencyCode = 'EUR';
        $opts->title = 'Integrationstest-AB '.date('d.m.Y H:i:s');

        $item = new ConfirmationItemCreateOptions(1.0, $faker->randomFloat(2, 10, 100));
        $item->title = 'Testposition';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $opts->addItem($item);

        $confirmation = $billomat->confirmations->create($opts);

        self::assertNotNull($confirmation->id);
        self::assertSame(ConfirmationStatus::DRAFT, $confirmation->status);

        // Cleanup
        self::assertTrue($billomat->confirmations->delete($confirmation->id));
        self::assertNull($billomat->confirmations->get($confirmation->id));
    }
}
