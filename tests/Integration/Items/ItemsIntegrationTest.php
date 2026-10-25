<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Items;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\ConfirmationItem;
use Justpilot\Billomat\Model\CreditNoteItem;
use Justpilot\Billomat\Model\DeliveryNoteItem;
use Justpilot\Billomat\Model\InvoiceItem;
use Justpilot\Billomat\Model\OfferItem;
use Justpilot\Billomat\Model\ReminderItem;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Sammel-Test: isolierte Item-CRUD-Pfade je Resource-Typ. RecurringItems werden
 * im Recurrings-Test abgedeckt, ReminderItems sind read-only (Mahnung-Positionen
 * werden vom System generiert).
 */
#[CoversNothing]
final class ItemsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureClientId(BillomatClient $billomat): int
    {
        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] !== $clients) {
            $existing = $clients[0]->id;
            self::assertNotNull($existing);

            return $existing;
        }

        $opts = new ClientCreateOptions(
            name: $this->faker()->company(),
            countryCode: 'DE',
            email: $this->faker()->unique()->safeEmail(),
        );
        $client = $billomat->clients->create($opts);
        $id = $client->id;
        self::assertNotNull($id);

        return $id;
    }

    #[Group('integration')]
    #[Test]
    public function canManageInvoiceItems(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $invOpts = new InvoiceCreateOptions(clientId: $this->ensureClientId($billomat), currencyCode: 'EUR');
        $seed = new InvoiceItemCreateOptions(1.0, 10.0, unit: 'Stück', title: 'Seed');
        $invOpts = $invOpts->withItem($seed);
        $invoice = $billomat->invoices->create($invOpts);
        self::assertNotNull($invoice->id);

        try {
            // Zusatz-Position über die ItemsApi
            $extra = new InvoiceItemCreateOptions(
                2.0,
                25.0,
                unit: 'Stunde',
                taxRate: 19.0,
                title: 'Zusatz',
            );
            $created = $billomat->invoices->items->create($invoice->id, $extra);
            self::assertNotNull($created->id);
            self::assertSame(2.0, $created->quantity);

            $update = new InvoiceItemCreateOptions(3.0, 25.0, title: 'Zusatz (geändert)');
            $updated = $billomat->invoices->items->update($created->id, $update);
            self::assertSame(3.0, $updated->quantity);

            $items = $billomat->invoices->items->list($invoice->id);
            self::assertContainsOnlyInstancesOf(InvoiceItem::class, $items);
            self::assertGreaterThanOrEqual(2, \count($items));

            $billomat->invoices->items->delete($created->id);
        } finally {
            $billomat->invoices->delete($invoice->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageOfferItems(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $offer = $billomat->offers->create(new OfferCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($offer->id);

        try {
            $opt = new OfferItemCreateOptions(1.0, 50.0, unit: 'Stück', title: 'Angebotsposition');
            $created = $billomat->offers->items->create($offer->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->offers->items->list($offer->id);
            self::assertContainsOnlyInstancesOf(OfferItem::class, $items);

            $billomat->offers->items->delete($created->id);
        } finally {
            $billomat->offers->delete($offer->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageConfirmationItems(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $conf = $billomat->confirmations->create(new ConfirmationCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($conf->id);

        try {
            $opt = new ConfirmationItemCreateOptions(1.0, 75.0, unit: 'Stück', title: 'Position');
            $created = $billomat->confirmations->items->create($conf->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->confirmations->items->list($conf->id);
            self::assertContainsOnlyInstancesOf(ConfirmationItem::class, $items);

            $billomat->confirmations->items->delete($created->id);
        } finally {
            $billomat->confirmations->delete($conf->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageCreditNoteItems(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $note = $billomat->creditNotes->create(new CreditNoteCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($note->id);

        try {
            $opt = new CreditNoteItemCreateOptions(1.0, 30.0, unit: 'Stück', title: 'Storno-Position');
            $created = $billomat->creditNotes->items->create($note->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->creditNotes->items->list($note->id);
            self::assertContainsOnlyInstancesOf(CreditNoteItem::class, $items);

            $billomat->creditNotes->items->delete($created->id);
        } finally {
            $billomat->creditNotes->delete($note->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageDeliveryNoteItems(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $note = $billomat->deliveryNotes->create(new DeliveryNoteCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($note->id);

        try {
            $opt = new DeliveryNoteItemCreateOptions(5.0, 12.0, unit: 'Stück', title: 'Lieferposition');
            $created = $billomat->deliveryNotes->items->create($note->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->deliveryNotes->items->list($note->id);
            self::assertContainsOnlyInstancesOf(DeliveryNoteItem::class, $items);

            $billomat->deliveryNotes->items->delete($created->id);
        } finally {
            $billomat->deliveryNotes->delete($note->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canListReminderItemsReadOnly(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        // Mahnungen brauchen OVERDUE-Rechnungen — bestehende Mahnung verwenden, sonst skip.
        $reminders = $billomat->reminders->list(['per_page' => 1]);
        if ([] === $reminders) {
            self::markTestSkipped('No reminders in sandbox – cannot probe reminder items.');
        }

        $items = $billomat->reminders->items->list($reminders[0]->id);

        self::assertIsArray($items);
        self::assertContainsOnlyInstancesOf(ReminderItem::class, $items);
    }
}
