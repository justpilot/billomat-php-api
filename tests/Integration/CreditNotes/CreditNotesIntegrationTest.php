<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\CreditNotes;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class CreditNotesIntegrationTest extends AbstractBillomatIntegrationTestCase
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

        return $created->id;
    }

    #[Group('integration')]
    #[Test]
    public function canListCreditNotesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $notes = $billomat->creditNotes->list(['per_page' => 5]);

        self::assertIsArray($notes);
        self::assertContainsOnlyInstancesOf(CreditNote::class, $notes);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateAndDeleteCreditNoteDraftInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();
        $faker = $this->faker();

        $opts = new CreditNoteCreateOptions(
            clientId: $clientId,
            date: new DateTimeImmutable('today'),
            title: 'Integrationstest-Gutschrift '.date('d.m.Y H:i:s'),
        );

        $item = new CreditNoteItemCreateOptions(
            1.0,
            Money::of((string) $faker->randomFloat(2, 5, 50)),
            title: 'Korrekturposition',
        );

        $opts = $opts->withItem($item);

        $note = $billomat->creditNotes->create($opts);

        self::assertSame(CreditNoteStatus::DRAFT, $note->status);

        $billomat->creditNotes->delete($note->id);
        self::assertNull($billomat->creditNotes->get($note->id));
    }
}
