<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Offers;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferUpdateOptions;
use Justpilot\Billomat\Model\Enum\OfferStatus;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class OffersIntegrationTest extends AbstractBillomatIntegrationTestCase
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

        $clientOptions = new ClientCreateOptions(
            name: $faker->company(),
            countryCode: 'DE',
            email: $faker->unique()->safeEmail(),
        );

        $created = $billomat->clients->create($clientOptions);

        return $created->id;
    }

    #[Group('integration')]
    #[Test]
    public function canListOffersFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $offers = $billomat->offers->list(['per_page' => 5]);

        self::assertIsArray($offers);
        self::assertContainsOnlyInstancesOf(Offer::class, $offers);

        if ([] !== $offers) {
            $first = $offers[0];
            self::assertIsInt($first->clientId);
            self::assertInstanceOf(OfferStatus::class, $first->status);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canCreateOfferDraftInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();
        $faker = $this->faker();

        $today = new DateTimeImmutable('today');

        $opts = new OfferCreateOptions(
            clientId: $clientId,
            date: $today,
            validityDays: 14,
            title: 'Integrationstest-Angebot '.date('d.m.Y H:i:s'),
            label: 'Leistungen Integrationstest',
            currencyCode: 'EUR',
        );

        $item = new OfferItemCreateOptions(
            quantity: 1.0,
            unitPrice: Money::of((string) $faker->randomFloat(2, 20, 100)),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Testposition',
        );

        $opts = $opts->withItem($item);

        $offer = $billomat->offers->create($opts);

        self::assertSame($clientId, $offer->clientId);
        self::assertSame(OfferStatus::DRAFT, $offer->status);

        // Cleanup
        $billomat->offers->delete($offer->id);
    }

    #[Group('integration')]
    #[Test]
    public function canUpdateAndDeleteDraftOfferInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();
        $faker = $this->faker();

        // Create draft
        $opts = new OfferCreateOptions(
            clientId: $clientId,
            title: 'Update-Test '.date('d.m.Y H:i:s'),
            currencyCode: 'EUR',
        );

        $item = new OfferItemCreateOptions(
            1.0,
            Money::of((string) $faker->randomFloat(2, 10, 50)),
            title: 'Update Test Position',
        );
        $opts = $opts->withItem($item);

        $draft = $billomat->offers->create($opts);

        // Update title via PUT
        $update = new OfferUpdateOptions(date: new DateTimeImmutable('today'), title: 'Geänderter Titel');

        $updated = $billomat->offers->update($draft->id, $update);

        self::assertSame($draft->id, $updated->id);
        self::assertSame('Geänderter Titel', $updated->title);

        // Delete
        $billomat->offers->delete($draft->id);
        self::assertNull($billomat->offers->get($draft->id));
    }

    #[Group('integration')]
    #[Test]
    public function canWalkThroughOfferStatusTransitionsInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();

        $opts = new OfferCreateOptions(
            clientId: $clientId,
            title: 'Status-Test '.date('d.m.Y H:i:s'),
            currencyCode: 'EUR',
        )->withItem(new OfferItemCreateOptions(1.0, Money::of('10.00'), title: 'Status-Test Position'));

        $offer = $billomat->offers->create($opts);
        $id = $offer->id;

        try {
            $billomat->offers->complete($id);
            self::assertSame(OfferStatus::OPEN, $billomat->offers->getOrFail($id)->status);

            $billomat->offers->cancel($id);
            self::assertSame(OfferStatus::CANCELED, $billomat->offers->getOrFail($id)->status);

            $billomat->offers->uncancel($id);
            self::assertSame(OfferStatus::OPEN, $billomat->offers->getOrFail($id)->status);

            $billomat->offers->win($id);
            self::assertSame(OfferStatus::WON, $billomat->offers->getOrFail($id)->status);

            // clear() geht erst aus WON, unclear() führt zurück nach WON.
            $billomat->offers->clear($id);
            self::assertSame(OfferStatus::CLEARED, $billomat->offers->getOrFail($id)->status);

            $billomat->offers->unclear($id);
            self::assertSame(OfferStatus::WON, $billomat->offers->getOrFail($id)->status);

            $billomat->offers->lose($id);
            self::assertSame(OfferStatus::LOST, $billomat->offers->getOrFail($id)->status);
        } finally {
            $billomat->offers->delete($id);
        }
    }
}
