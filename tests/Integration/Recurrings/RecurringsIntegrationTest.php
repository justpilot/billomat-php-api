<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Recurrings;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringItemUpdateOptions;
use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\Api\RecurringUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Model\Money;
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
        $opts = new ClientCreateOptions(
            name: $faker->company(),
            countryCode: 'DE',
            email: $faker->unique()->safeEmail(),
        );

        $client = $billomat->clients->create($opts);

        return $client->id;
    }

    private function buildSafeDraft(BillomatClient $billomat): RecurringCreateOptions
    {
        $clientId = $this->ensureClientId($billomat);

        // Sicherer Draft: action=CREATE erzeugt nur eine Entwurfs-Rechnung beim Lauf
        // (kein Versand). Startdatum weit in der Zukunft, damit nichts ungewollt
        // ausgeführt wird, falls Cleanup hängenbleibt.
        $opts = new RecurringCreateOptions(
            clientId: $clientId,
            name: 'IT-Recurring-'.date('YmdHis'),
            title: 'Integrationstest Abo',
            currencyCode: 'EUR',
            action: RecurringAction::CREATE,
            cycleNumber: 1,
            cycle: RecurringCycle::MONTHLY,
            startDate: new DateTimeImmutable('+10 years'),
        );

        $item = new RecurringItemCreateOptions(
            quantity: 1.0,
            unitPrice: Money::of('50'),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Abo-Position',
        );
        $opts = $opts->withItem($item);

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

        self::assertSame(RecurringAction::CREATE, $created->action);
        self::assertSame(RecurringCycle::MONTHLY, $created->cycle);

        try {
            // Update: Titel ändern
            $update = new RecurringUpdateOptions(title: 'Integrationstest Abo (geändert)');
            $updated = $billomat->recurrings->update($created->id, $update);

            self::assertSame($created->id, $updated->id);
            self::assertSame('Integrationstest Abo (geändert)', $updated->title);

            // Get
            $fetched = $billomat->recurrings->get($created->id);
            self::assertInstanceOf(Recurring::class, $fetched);
            self::assertSame($created->id, $fetched->id);
        } finally {
            $billomat->recurrings->delete($created->id);
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
            $newItem = new RecurringItemCreateOptions(
                quantity: 2.0,
                unitPrice: Money::of('25'),
                unit: 'Stück',
                taxRate: 19.0,
                title: 'Zusatz-Position',
            );

            $created = $billomat->recurrings->items->create($recurring->id, $newItem);
            self::assertNotNull($created->id);
            self::assertSame(2.0, $created->quantity);

            // Update der Position
            $updateItem = new RecurringItemUpdateOptions(
                quantity: 3.0,
                title: 'Zusatz-Position (geändert)',
            );
            $updated = $billomat->recurrings->items->update($created->id, $updateItem);
            self::assertSame(3.0, $updated->quantity);

            // List
            $items = $billomat->recurrings->items->list($recurring->id);
            self::assertContainsOnlyInstancesOf(RecurringItem::class, $items);
            self::assertGreaterThanOrEqual(2, \count($items));

            // Cleanup item
            $billomat->recurrings->items->delete($created->id);
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
            $tagOpts = new RecurringTagCreateOptions(name: 'IT-Tag-'.date('His'));
            $tag = $billomat->recurrings->tags->create($recurring->id, $tagOpts);
            self::assertNotNull($tag->id);

            $tags = $billomat->recurrings->tags->list($recurring->id);
            self::assertContainsOnlyInstancesOf(RecurringTag::class, $tags);
            self::assertGreaterThanOrEqual(1, \count($tags));

            $cloud = $billomat->recurrings->tags->cloud();
            self::assertContainsOnlyInstancesOf(RecurringTagCloudEntry::class, $cloud);

            $billomat->recurrings->tags->delete($tag->id);
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
        $opts = $this->buildSafeDraft($billomat)
            ->withAction(RecurringAction::EMAIL)
            ->withEmailSubject('Integrationstest Abo-Versand')
            ->withEmailMessage('Auto-Test');

        $recurring = $billomat->recurrings->create($opts);

        try {
            $receiverOpts = new RecurringEmailReceiverCreateOptions(
                type: RecurringEmailReceiverType::CC,
                address: 'integration-test-'.date('His').'@example.com',
            );
            $receiver = $billomat->recurrings->emailReceivers->create($recurring->id, $receiverOpts);
            self::assertSame(RecurringEmailReceiverType::CC, $receiver->type);

            $receivers = $billomat->recurrings->emailReceivers->list($recurring->id);
            self::assertContainsOnlyInstancesOf(RecurringEmailReceiver::class, $receivers);
            self::assertGreaterThanOrEqual(1, \count($receivers));

            $billomat->recurrings->emailReceivers->delete($receiver->id);
        } finally {
            $billomat->recurrings->delete($recurring->id);
        }
    }
}
