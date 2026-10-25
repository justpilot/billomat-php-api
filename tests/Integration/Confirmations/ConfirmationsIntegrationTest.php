<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Confirmations;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use Justpilot\Billomat\Model\Money;
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
            $id = $clients[0]->id;
            self::assertNotNull($id);

            return $id;
        }

        $faker = $this->faker();

        $opts = new ClientCreateOptions(
            name: $faker->company(),
            countryCode: 'DE',
            email: $faker->unique()->safeEmail(),
        );

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

        $opts = new ConfirmationCreateOptions(
            clientId: $clientId,
            date: new DateTimeImmutable('today'),
            title: 'Integrationstest-AB '.date('d.m.Y H:i:s'),
            currencyCode: 'EUR',
        );

        $item = new ConfirmationItemCreateOptions(
            1.0,
            Money::of((string) $faker->randomFloat(2, 10, 100)),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Testposition',
        );

        $opts = $opts->withItem($item);

        $confirmation = $billomat->confirmations->create($opts);

        self::assertNotNull($confirmation->id);
        self::assertSame(ConfirmationStatus::DRAFT, $confirmation->status);

        // Cleanup
        $billomat->confirmations->delete($confirmation->id);
        self::assertNull($billomat->confirmations->get($confirmation->id));
    }

    #[Group('integration')]
    #[Test]
    public function canWalkThroughConfirmationStatusTransitionsInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();

        $opts = new ConfirmationCreateOptions(
            clientId: $clientId,
            title: 'Status-Test '.date('d.m.Y H:i:s'),
            currencyCode: 'EUR',
        )->withItem(new ConfirmationItemCreateOptions(1.0, Money::of('10.00'), title: 'Status-Test Position'));

        $confirmation = $billomat->confirmations->create($opts);
        self::assertNotNull($confirmation->id);
        $id = $confirmation->id;

        try {
            $billomat->confirmations->complete($id);
            self::assertSame(ConfirmationStatus::COMPLETED, $billomat->confirmations->getOrFail($id)->status);

            $billomat->confirmations->cancel($id);
            self::assertSame(ConfirmationStatus::CANCELED, $billomat->confirmations->getOrFail($id)->status);

            $billomat->confirmations->uncancel($id);
            self::assertSame(ConfirmationStatus::COMPLETED, $billomat->confirmations->getOrFail($id)->status);

            $billomat->confirmations->clear($id);
            self::assertSame(ConfirmationStatus::CLEARED, $billomat->confirmations->getOrFail($id)->status);

            $billomat->confirmations->unclear($id);
            self::assertSame(ConfirmationStatus::COMPLETED, $billomat->confirmations->getOrFail($id)->status);
        } finally {
            $billomat->confirmations->delete($id);
        }
    }
}
