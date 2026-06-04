<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Recurrings;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\Api\RecurringUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Model\RecurringEmailReceiver;
use Justpilot\Billomat\Model\RecurringItem;
use Justpilot\Billomat\Model\RecurringTag;
use Justpilot\Billomat\Model\RecurringTagCloudEntry;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class RecurringsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureClientId(BillomatClient $billomat): int
    {
        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] !== $clients) {
            $existing = $clients[0]->id;
            self::assertNotNull($existing);

            return $existing;
        }

        $faker = $this->faker();
        $opts = new ClientCreateOptions();
        $opts->name = $faker->company();
        $opts->email = $faker->unique()->safeEmail();
        $opts->countryCode = 'DE';

        $client = $billomat->clients->create($opts);
        $id = $client->id;
        self::assertNotNull($id);

        return $id;
    }

    private function buildSafeDraft(BillomatClient $billomat): RecurringCreateOptions
    {
        $clientId = $this->ensureClientId($billomat);

        // Sicherer Draft: action=CREATE erzeugt nur eine Entwurfs-Rechnung beim Lauf
        // (kein Versand). Startdatum weit in der Zukunft, damit nichts ungewollt
        // ausgeführt wird, falls Cleanup hängenbleibt.
        $opts = new RecurringCreateOptions(clientId: $clientId);
        $opts->name = 'IT-Recurring-'.date('YmdHis');
        $opts->title = 'Integrationstest Abo';
        $opts->currencyCode = 'EUR';
        $opts->action = RecurringAction::CREATE;
        $opts->cycle = RecurringCycle::MONTHLY;
        $opts->cycleNumber = 1;
        $opts->startDate = new DateTimeImmutable('+10 years');

        $item = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 50.0);
        $item->title = 'Abo-Position';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;
        $opts->addItem($item);

        return $opts;
    }

    #[Group('integration')]
    #[Test]
    public function canListRecurringsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $recurrings = $billomat->recurrings->list(['per_page' => 5]);

        self::assertIsArray($recurrings);
        self::assertContainsOnlyInstancesOf(Recurring::class, $recurrings);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateDeleteRecurringInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $created = $billomat->recurrings->create($this->buildSafeDraft($billomat));

        self::assertNotNull($created->id);
        self::assertSame(RecurringAction::CREATE, $created->action);
        self::assertSame(RecurringCycle::MONTHLY, $created->cycle);

        try {
            // Update: Titel ändern
            $update = new RecurringUpdateOptions();
            $update->title = 'Integrationstest Abo (geändert)';
            $updated = $billomat->recurrings->update($created->id, $update);

            self::assertSame($created->id, $updated->id);
            self::assertSame('Integrationstest Abo (geändert)', $updated->title);

            // Get
            $fetched = $billomat->recurrings->get($created->id);
            self::assertInstanceOf(Recurring::class, $fetched);
            self::assertSame($created->id, $fetched->id);
        } finally {
            self::assertTrue($billomat->recurrings->delete($created->id));
        }

        self::assertNull($billomat->recurrings->get($created->id));
    }

    #[Group('integration')]
    #[Test]
    public function canManageRecurringItemsInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $recurring = $billomat->recurrings->create($this->buildSafeDraft($billomat));

        try {
            // Zusätzliche Position über die ItemsApi anlegen
            $newItem = new RecurringItemCreateOptions(quantity: 2.0, unitPrice: 25.0);
            $newItem->title = 'Zusatz-Position';
            $newItem->unit = 'Stück';
            $newItem->taxRate = 19.0;

            $created = $billomat->recurringItems->create($recurring->id, $newItem);
            self::assertNotNull($created->id);
            self::assertSame(2.0, $created->quantity);

            // Update der Position
            $updateItem = new RecurringItemCreateOptions(quantity: 3.0, unitPrice: 25.0);
            $updateItem->title = 'Zusatz-Position (geändert)';
            $updated = $billomat->recurringItems->update($created->id, $updateItem);
            self::assertSame(3.0, $updated->quantity);

            // List
            $items = $billomat->recurringItems->listByRecurring($recurring->id);
            self::assertContainsOnlyInstancesOf(RecurringItem::class, $items);
            self::assertGreaterThanOrEqual(2, \count($items));

            // Cleanup item
            self::assertTrue($billomat->recurringItems->delete($created->id));
        } finally {
            $billomat->recurrings->delete($recurring->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageRecurringTagsInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $recurring = $billomat->recurrings->create($this->buildSafeDraft($billomat));

        try {
            $tagOpts = new RecurringTagCreateOptions(recurringId: $recurring->id, name: 'IT-Tag-'.date('His'));
            $tag = $billomat->recurringTags->create($tagOpts);
            self::assertNotNull($tag->id);

            $tags = $billomat->recurringTags->listByRecurring($recurring->id);
            self::assertContainsOnlyInstancesOf(RecurringTag::class, $tags);
            self::assertGreaterThanOrEqual(1, \count($tags));

            $cloud = $billomat->recurringTags->cloud();
            self::assertContainsOnlyInstancesOf(RecurringTagCloudEntry::class, $cloud);

            self::assertTrue($billomat->recurringTags->delete($tag->id));
        } finally {
            $billomat->recurrings->delete($recurring->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageRecurringEmailReceiversInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        // E-Mail-Empfänger setzen ein Recurring voraus, das überhaupt Mails verschickt.
        // Daher action=EMAIL mit minimalen E-Mail-Feldern. startDate weit in der Zukunft
        // verhindert ungewollten Versand.
        $opts = $this->buildSafeDraft($billomat);
        $opts->action = RecurringAction::EMAIL;
        $opts->emailSubject = 'Integrationstest Abo-Versand';
        $opts->emailMessage = 'Auto-Test';

        $recurring = $billomat->recurrings->create($opts);

        try {
            $receiverOpts = new RecurringEmailReceiverCreateOptions(
                recurringId: $recurring->id,
                type: RecurringEmailReceiverType::CC,
                address: 'integration-test-'.date('His').'@example.com',
            );
            $receiver = $billomat->recurringEmailReceivers->create($receiverOpts);
            self::assertNotNull($receiver->id);
            self::assertSame(RecurringEmailReceiverType::CC, $receiver->type);

            $receivers = $billomat->recurringEmailReceivers->listByRecurring($recurring->id);
            self::assertContainsOnlyInstancesOf(RecurringEmailReceiver::class, $receivers);
            self::assertGreaterThanOrEqual(1, \count($receivers));

            self::assertTrue($billomat->recurringEmailReceivers->delete($receiver->id));
        } finally {
            $billomat->recurrings->delete($recurring->id);
        }
    }
}
