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

        $opts = new ClientCreateOptions();
        $opts->name = $this->faker()->company();
        $opts->email = $this->faker()->unique()->safeEmail();
        $opts->countryCode = 'DE';
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

        $invOpts = new InvoiceCreateOptions(clientId: $this->ensureClientId($billomat));
        $invOpts->currencyCode = 'EUR';
        $seed = new InvoiceItemCreateOptions(1.0, 10.0);
        $seed->title = 'Seed';
        $seed->unit = 'Stück';
        $invOpts->addItem($seed);
        $invoice = $billomat->invoices->create($invOpts);
        self::assertNotNull($invoice->id);

        try {
            // Zusatz-Position über die ItemsApi
            $extra = new InvoiceItemCreateOptions(2.0, 25.0);
            $extra->title = 'Zusatz';
            $extra->unit = 'Stunde';
            $extra->taxRate = 19.0;
            $created = $billomat->invoiceItems->create($invoice->id, $extra);
            self::assertNotNull($created->id);
            self::assertSame(2.0, $created->quantity);

            $update = new InvoiceItemCreateOptions(3.0, 25.0);
            $update->title = 'Zusatz (geändert)';
            $updated = $billomat->invoiceItems->update($created->id, $update);
            self::assertSame(3.0, $updated->quantity);

            $items = $billomat->invoiceItems->listByInvoice($invoice->id);
            self::assertContainsOnlyInstancesOf(InvoiceItem::class, $items);
            self::assertGreaterThanOrEqual(2, \count($items));

            self::assertTrue($billomat->invoiceItems->delete($created->id));
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
            $opt = new OfferItemCreateOptions(1.0, 50.0);
            $opt->title = 'Angebotsposition';
            $opt->unit = 'Stück';
            $created = $billomat->offerItems->create($offer->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->offerItems->listByOffer($offer->id);
            self::assertContainsOnlyInstancesOf(OfferItem::class, $items);

            self::assertTrue($billomat->offerItems->delete($created->id));
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
            $opt = new ConfirmationItemCreateOptions(1.0, 75.0);
            $opt->title = 'Position';
            $opt->unit = 'Stück';
            $created = $billomat->confirmationItems->create($conf->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->confirmationItems->listByConfirmation($conf->id);
            self::assertContainsOnlyInstancesOf(ConfirmationItem::class, $items);

            self::assertTrue($billomat->confirmationItems->delete($created->id));
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
            $opt = new CreditNoteItemCreateOptions(1.0, 30.0);
            $opt->title = 'Storno-Position';
            $opt->unit = 'Stück';
            $created = $billomat->creditNoteItems->create($note->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->creditNoteItems->listByCreditNote($note->id);
            self::assertContainsOnlyInstancesOf(CreditNoteItem::class, $items);

            self::assertTrue($billomat->creditNoteItems->delete($created->id));
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
            $opt = new DeliveryNoteItemCreateOptions(5.0, 12.0);
            $opt->title = 'Lieferposition';
            $opt->unit = 'Stück';
            $created = $billomat->deliveryNoteItems->create($note->id, $opt);
            self::assertNotNull($created->id);

            $items = $billomat->deliveryNoteItems->listByDeliveryNote($note->id);
            self::assertContainsOnlyInstancesOf(DeliveryNoteItem::class, $items);

            self::assertTrue($billomat->deliveryNoteItems->delete($created->id));
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

        $items = $billomat->reminderItems->listByReminder($reminders[0]->id);

        self::assertIsArray($items);
        self::assertContainsOnlyInstancesOf(ReminderItem::class, $items);
    }
}
