<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\DeliveryNotes;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class DeliveryNotesIntegrationTest extends AbstractBillomatIntegrationTestCase
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
    public function canListDeliveryNotesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $notes = $billomat->deliveryNotes->list(['per_page' => 5]);

        self::assertIsArray($notes);
        self::assertContainsOnlyInstancesOf(DeliveryNote::class, $notes);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateAndDeleteDeliveryNoteDraftInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();
        $faker = $this->faker();

        $opts = new DeliveryNoteCreateOptions(clientId: $clientId);
        $opts->date = new DateTimeImmutable('today');
        $opts->title = 'Integrationstest-LS '.date('d.m.Y H:i:s');

        $item = new DeliveryNoteItemCreateOptions(1.0, $faker->randomFloat(2, 5, 50));
        $item->title = 'Lieferposition';
        $item->unit = 'Stück';

        $opts->addItem($item);

        $note = $billomat->deliveryNotes->create($opts);

        self::assertInstanceOf(DeliveryNote::class, $note);
        self::assertNotNull($note->id);
        self::assertSame(DeliveryNoteStatus::DRAFT, $note->status);

        self::assertTrue($billomat->deliveryNotes->delete($note->id));
        self::assertNull($billomat->deliveryNotes->get($note->id));
    }
}
