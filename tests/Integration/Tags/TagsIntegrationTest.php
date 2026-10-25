<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Tags;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationTagCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteTagCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingTagCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\LetterTagCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\ReminderTagCreateOptions;
use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\ConfirmationTag;
use Justpilot\Billomat\Model\ConfirmationTagCloudEntry;
use Justpilot\Billomat\Model\CreditNoteTag;
use Justpilot\Billomat\Model\CreditNoteTagCloudEntry;
use Justpilot\Billomat\Model\DeliveryNoteTag;
use Justpilot\Billomat\Model\DeliveryNoteTagCloudEntry;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\IncomingTag;
use Justpilot\Billomat\Model\IncomingTagCloudEntry;
use Justpilot\Billomat\Model\InvoiceTag;
use Justpilot\Billomat\Model\InvoiceTagCloudEntry;
use Justpilot\Billomat\Model\LetterTag;
use Justpilot\Billomat\Model\LetterTagCloudEntry;
use Justpilot\Billomat\Model\OfferTag;
use Justpilot\Billomat\Model\OfferTagCloudEntry;
use Justpilot\Billomat\Model\ReminderTag;
use Justpilot\Billomat\Model\ReminderTagCloudEntry;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Sammel-Test: isolierte Tag-CRUD-Pfade je Resource-Typ.
 *
 * Article-, Client-, Supplier- und Recurring-Tags werden bereits in ihren
 * jeweiligen Resource-Integration-Tests abgedeckt und hier nicht dupliziert.
 */
#[CoversNothing]
final class TagsIntegrationTest extends AbstractBillomatIntegrationTestCase
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

    private function ensureSupplierId(BillomatClient $billomat): int
    {
        $suppliers = $billomat->suppliers->list(['per_page' => 1]);
        if ([] !== $suppliers) {
            $existing = $suppliers[0]->id;
            self::assertNotNull($existing);

            return $existing;
        }

        $opts = new SupplierCreateOptions(name: 'IT-Lieferant-TagTest '.date('His'));
        $opts->countryCode = 'DE';
        $created = $billomat->suppliers->create($opts);
        $id = $created->id;
        self::assertNotNull($id);

        return $id;
    }

    private function createDraftInvoiceId(BillomatClient $billomat): int
    {
        $opts = new InvoiceCreateOptions(clientId: $this->ensureClientId($billomat));
        $opts->currencyCode = 'EUR';
        $opts->title = 'IT-TagTest '.date('His');
        $item = new InvoiceItemCreateOptions(1.0, 10.0);
        $item->title = 'Pos';
        $item->unit = 'Stück';
        $opts->addItem($item);

        $invoice = $billomat->invoices->create($opts);
        self::assertNotNull($invoice->id);

        return $invoice->id;
    }

    #[Group('integration')]
    #[Test]
    public function canManageInvoiceTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $invoiceId = $this->createDraftInvoiceId($billomat);

        try {
            $tag = $billomat->invoices->tags->create(new InvoiceTagCreateOptions($invoiceId, 'IT-InvTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->invoices->tags->list($invoiceId);
            self::assertContainsOnlyInstancesOf(InvoiceTag::class, $tags);

            $cloud = $billomat->invoices->tags->cloud();
            self::assertContainsOnlyInstancesOf(InvoiceTagCloudEntry::class, $cloud);

            $billomat->invoices->tags->delete($tag->id);
        } finally {
            $billomat->invoices->delete($invoiceId);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageOfferTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $offer = $billomat->offers->create(new OfferCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($offer->id);

        try {
            $tag = $billomat->offers->tags->create(new OfferTagCreateOptions($offer->id, 'IT-OffTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->offers->tags->list($offer->id);
            self::assertContainsOnlyInstancesOf(OfferTag::class, $tags);

            $cloud = $billomat->offers->tags->cloud();
            self::assertContainsOnlyInstancesOf(OfferTagCloudEntry::class, $cloud);

            $billomat->offers->tags->delete($tag->id);
        } finally {
            $billomat->offers->delete($offer->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageConfirmationTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $conf = $billomat->confirmations->create(new ConfirmationCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($conf->id);

        try {
            $tag = $billomat->confirmations->tags->create(new ConfirmationTagCreateOptions($conf->id, 'IT-ConfTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->confirmations->tags->list($conf->id);
            self::assertContainsOnlyInstancesOf(ConfirmationTag::class, $tags);

            $cloud = $billomat->confirmations->tags->cloud();
            self::assertContainsOnlyInstancesOf(ConfirmationTagCloudEntry::class, $cloud);

            $billomat->confirmations->tags->delete($tag->id);
        } finally {
            $billomat->confirmations->delete($conf->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageCreditNoteTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $note = $billomat->creditNotes->create(new CreditNoteCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($note->id);

        try {
            $tag = $billomat->creditNotes->tags->create(new CreditNoteTagCreateOptions($note->id, 'IT-GsTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->creditNotes->tags->list($note->id);
            self::assertContainsOnlyInstancesOf(CreditNoteTag::class, $tags);

            $cloud = $billomat->creditNotes->tags->cloud();
            self::assertContainsOnlyInstancesOf(CreditNoteTagCloudEntry::class, $cloud);

            $billomat->creditNotes->tags->delete($tag->id);
        } finally {
            $billomat->creditNotes->delete($note->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageDeliveryNoteTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $note = $billomat->deliveryNotes->create(new DeliveryNoteCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($note->id);

        try {
            $tag = $billomat->deliveryNotes->tags->create(new DeliveryNoteTagCreateOptions($note->id, 'IT-LsTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->deliveryNotes->tags->list($note->id);
            self::assertContainsOnlyInstancesOf(DeliveryNoteTag::class, $tags);

            $cloud = $billomat->deliveryNotes->tags->cloud();
            self::assertContainsOnlyInstancesOf(DeliveryNoteTagCloudEntry::class, $cloud);

            $billomat->deliveryNotes->tags->delete($tag->id);
        } finally {
            $billomat->deliveryNotes->delete($note->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageLetterTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $letter = $billomat->letters->create(new LetterCreateOptions(clientId: $this->ensureClientId($billomat)));
        self::assertNotNull($letter->id);

        try {
            $tag = $billomat->letters->tags->create(new LetterTagCreateOptions($letter->id, 'IT-BrTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->letters->tags->list($letter->id);
            self::assertContainsOnlyInstancesOf(LetterTag::class, $tags);

            $cloud = $billomat->letters->tags->cloud();
            self::assertContainsOnlyInstancesOf(LetterTagCloudEntry::class, $cloud);

            $billomat->letters->tags->delete($tag->id);
        } finally {
            $billomat->letters->delete($letter->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageIncomingTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $incomingOpts = new IncomingCreateOptions(supplierId: $this->ensureSupplierId($billomat));
        $incomingOpts->incomingNumber = 'IT-IN-Tag-'.date('His');
        $incoming = $billomat->incomings->create($incomingOpts);
        self::assertNotNull($incoming->id);

        try {
            $tag = $billomat->incomings->tags->create(new IncomingTagCreateOptions($incoming->id, 'IT-InTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->incomings->tags->list($incoming->id);
            self::assertContainsOnlyInstancesOf(IncomingTag::class, $tags);

            $cloud = $billomat->incomings->tags->cloud();
            self::assertContainsOnlyInstancesOf(IncomingTagCloudEntry::class, $cloud);

            $billomat->incomings->tags->delete($tag->id);
        } finally {
            $billomat->incomings->delete($incoming->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageReminderTags(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        // Reminders brauchen eine OVERDUE-Rechnung in der Sandbox.
        $overdue = $billomat->invoices->list(['per_page' => 1, 'status' => InvoiceStatus::OVERDUE->value]);
        if ([] === $overdue) {
            self::markTestSkipped('No OVERDUE invoice in sandbox – reminder creation requires one.');
        }

        $reminder = $billomat->reminders->create(new ReminderCreateOptions(invoiceId: $overdue[0]->id));
        self::assertNotNull($reminder->id);

        try {
            $tag = $billomat->reminders->tags->create(new ReminderTagCreateOptions($reminder->id, 'IT-MaTag-'.date('His')));
            self::assertNotNull($tag->id);

            $tags = $billomat->reminders->tags->list($reminder->id);
            self::assertContainsOnlyInstancesOf(ReminderTag::class, $tags);

            $cloud = $billomat->reminders->tags->cloud();
            self::assertContainsOnlyInstancesOf(ReminderTagCloudEntry::class, $cloud);

            $billomat->reminders->tags->delete($tag->id);
        } finally {
            $billomat->reminders->delete($reminder->id);
        }
    }
}
