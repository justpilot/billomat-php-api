<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Offers;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferUpdateOptions;
use Justpilot\Billomat\Model\Enum\OfferStatus;
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
            return $clients[0]->id;
        }

        $faker = $this->faker();

        $clientOptions = new ClientCreateOptions();
        $clientOptions->name = $faker->company();
        $clientOptions->email = $faker->unique()->safeEmail();
        $clientOptions->countryCode = 'DE';

        $created = $billomat->clients->create($clientOptions);

        self::assertNotNull($created->id);

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
            self::assertNotNull($first->id);
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

        $opts = new OfferCreateOptions(clientId: $clientId);
        $opts->date = $today;
        $opts->validityDays = 14;
        $opts->currencyCode = 'EUR';
        $opts->title = 'Integrationstest-Angebot '.date('d.m.Y H:i:s');
        $opts->label = 'Leistungen Integrationstest';

        $item = new OfferItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
        );
        $item->title = 'Testposition';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $opts->addItem($item);

        $offer = $billomat->offers->create($opts);

        self::assertNotNull($offer->id);
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
        $opts = new OfferCreateOptions(clientId: $clientId);
        $opts->currencyCode = 'EUR';
        $opts->title = 'Update-Test '.date('d.m.Y H:i:s');

        $item = new OfferItemCreateOptions(1.0, $faker->randomFloat(2, 10, 50));
        $item->title = 'Update Test Position';
        $opts->addItem($item);

        $draft = $billomat->offers->create($opts);

        self::assertNotNull($draft->id);

        // Update title via PUT
        $update = new OfferUpdateOptions();
        $update->title = 'Geänderter Titel';
        $update->date = new DateTimeImmutable('today');

        $updated = $billomat->offers->update($draft->id, $update);

        self::assertSame($draft->id, $updated->id);
        self::assertSame('Geänderter Titel', $updated->title);

        // Delete
        self::assertTrue($billomat->offers->delete($draft->id));
        self::assertNull($billomat->offers->get($draft->id));
    }
}
