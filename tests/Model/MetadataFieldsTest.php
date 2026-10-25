<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Closure;
use DateTimeImmutable;
use Generator;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Article;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use Justpilot\Billomat\Model\ArticleTag;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\ClientProperty;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Model\ClientTag;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\ConfirmationItem;
use Justpilot\Billomat\Model\ConfirmationTag;
use Justpilot\Billomat\Model\Contact;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\CreditNoteItem;
use Justpilot\Billomat\Model\CreditNotePayment;
use Justpilot\Billomat\Model\CreditNoteTag;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\DeliveryNoteItem;
use Justpilot\Billomat\Model\DeliveryNoteTag;
use Justpilot\Billomat\Model\EmailTemplate;
use Justpilot\Billomat\Model\Enum\ArticleType;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use Justpilot\Billomat\Model\Enum\LetterStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use Justpilot\Billomat\Model\Enum\OfferStatus;
use Justpilot\Billomat\Model\Enum\PropertyType;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\ReminderStatus;
use Justpilot\Billomat\Model\Enum\SupplyDateType;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateType;
use Justpilot\Billomat\Model\FreeText;
use Justpilot\Billomat\Model\InboxDocument;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Model\IncomingComment;
use Justpilot\Billomat\Model\IncomingPayment;
use Justpilot\Billomat\Model\IncomingProperty;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Model\IncomingTag;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceComment;
use Justpilot\Billomat\Model\InvoiceItem;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Model\InvoiceTag;
use Justpilot\Billomat\Model\Letter;
use Justpilot\Billomat\Model\LetterComment;
use Justpilot\Billomat\Model\LetterTag;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferComment;
use Justpilot\Billomat\Model\OfferItem;
use Justpilot\Billomat\Model\OfferTag;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Model\RecurringItem;
use Justpilot\Billomat\Model\RecurringTag;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\ReminderItem;
use Justpilot\Billomat\Model\ReminderTag;
use Justpilot\Billomat\Model\ReminderText;
use Justpilot\Billomat\Model\Supplier;
use Justpilot\Billomat\Model\SupplierProperty;
use Justpilot\Billomat\Model\SupplierPropertyValue;
use Justpilot\Billomat\Model\SupplierTag;
use Justpilot\Billomat\Model\TaxRate;
use Justpilot\Billomat\Model\Template;
use Justpilot\Billomat\Model\Unit;
use Justpilot\Billomat\Model\User;
use Justpilot\Billomat\Model\UserProperty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die Metadaten-Felder `created`, `updated` und `customfield`, die
 * Billomat (Stand 2026-10) auf fast allen Ressourcen mitliefert.
 *
 * Pro Model sind nur die Felder gelistet, die die API für diese Ressource
 * tatsächlich zurückgibt.
 */
#[CoversClass(Article::class)]
#[CoversClass(ArticleProperty::class)]
#[CoversClass(ArticlePropertyValue::class)]
#[CoversClass(ArticleTag::class)]
#[CoversClass(Client::class)]
#[CoversClass(ClientProperty::class)]
#[CoversClass(ClientPropertyValue::class)]
#[CoversClass(ClientTag::class)]
#[CoversClass(Confirmation::class)]
#[CoversClass(ConfirmationComment::class)]
#[CoversClass(ConfirmationItem::class)]
#[CoversClass(ConfirmationTag::class)]
#[CoversClass(Contact::class)]
#[CoversClass(CreditNote::class)]
#[CoversClass(CreditNoteComment::class)]
#[CoversClass(CreditNoteItem::class)]
#[CoversClass(CreditNotePayment::class)]
#[CoversClass(CreditNoteTag::class)]
#[CoversClass(DeliveryNote::class)]
#[CoversClass(DeliveryNoteComment::class)]
#[CoversClass(DeliveryNoteItem::class)]
#[CoversClass(DeliveryNoteTag::class)]
#[CoversClass(EmailTemplate::class)]
#[CoversClass(FreeText::class)]
#[CoversClass(InboxDocument::class)]
#[CoversClass(Incoming::class)]
#[CoversClass(IncomingComment::class)]
#[CoversClass(IncomingPayment::class)]
#[CoversClass(IncomingProperty::class)]
#[CoversClass(IncomingPropertyValue::class)]
#[CoversClass(IncomingTag::class)]
#[CoversClass(Invoice::class)]
#[CoversClass(InvoiceComment::class)]
#[CoversClass(InvoiceItem::class)]
#[CoversClass(InvoicePayment::class)]
#[CoversClass(InvoiceTag::class)]
#[CoversClass(Letter::class)]
#[CoversClass(LetterComment::class)]
#[CoversClass(LetterTag::class)]
#[CoversClass(Offer::class)]
#[CoversClass(OfferComment::class)]
#[CoversClass(OfferItem::class)]
#[CoversClass(OfferTag::class)]
#[CoversClass(Recurring::class)]
#[CoversClass(RecurringItem::class)]
#[CoversClass(RecurringTag::class)]
#[CoversClass(Reminder::class)]
#[CoversClass(ReminderItem::class)]
#[CoversClass(ReminderTag::class)]
#[CoversClass(ReminderText::class)]
#[CoversClass(Supplier::class)]
#[CoversClass(SupplierProperty::class)]
#[CoversClass(SupplierPropertyValue::class)]
#[CoversClass(SupplierTag::class)]
#[CoversClass(TaxRate::class)]
#[CoversClass(Template::class)]
#[CoversClass(Unit::class)]
#[CoversClass(User::class)]
#[CoversClass(UserProperty::class)]
#[UsesClass(ArticleType::class)]
#[UsesClass(ListCaster::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(ConfirmationCommentActionKey::class)]
#[UsesClass(ConfirmationStatus::class)]
#[UsesClass(CreditNoteCommentActionKey::class)]
#[UsesClass(CreditNoteStatus::class)]
#[UsesClass(DeliveryNoteCommentActionKey::class)]
#[UsesClass(DeliveryNoteStatus::class)]
#[UsesClass(IncomingCommentActionKey::class)]
#[UsesClass(IncomingStatus::class)]
#[UsesClass(InvoiceCommentActionKey::class)]
#[UsesClass(InvoiceItemType::class)]
#[UsesClass(InvoicePaymentType::class)]
#[UsesClass(InvoiceStatus::class)]
#[UsesClass(LetterCommentActionKey::class)]
#[UsesClass(LetterStatus::class)]
#[UsesClass(NetGross::class)]
#[UsesClass(OfferCommentActionKey::class)]
#[UsesClass(OfferStatus::class)]
#[UsesClass(PropertyType::class)]
#[UsesClass(RecurringAction::class)]
#[UsesClass(RecurringCycle::class)]
#[UsesClass(ReminderStatus::class)]
#[UsesClass(SupplyDateType::class)]
#[UsesClass(TemplateDocumentType::class)]
#[UsesClass(TemplateFormat::class)]
#[UsesClass(TemplateType::class)]
#[UsesClass(Money::class)]
final class MetadataFieldsTest extends TestCase
{
    /**
     * Pro Model: `fromArray()` und die erwarteten Metadaten-Keys.
     *
     * @return Generator<string, array{Closure(array<string,mixed>): object, list<string>}>
     */
    public static function models(): Generator
    {
        yield 'Article' => [Article::fromArray(...), ['updated', 'customfield']];
        yield 'ArticleProperty' => [ArticleProperty::fromArray(...), ['customfield']];
        yield 'ArticlePropertyValue' => [ArticlePropertyValue::fromArray(...), ['created', 'updated', 'customfield']];
        yield 'ArticleTag' => [ArticleTag::fromArray(...), ['customfield']];
        yield 'Client' => [Client::fromArray(...), ['updated', 'customfield']];
        yield 'ClientProperty' => [ClientProperty::fromArray(...), ['customfield']];
        yield 'ClientPropertyValue' => [ClientPropertyValue::fromArray(...), ['created', 'updated', 'customfield']];
        yield 'ClientTag' => [ClientTag::fromArray(...), ['customfield']];
        yield 'Confirmation' => [Confirmation::fromArray(...), ['updated', 'customfield']];
        yield 'ConfirmationComment' => [ConfirmationComment::fromArray(...), ['customfield']];
        yield 'ConfirmationItem' => [ConfirmationItem::fromArray(...), ['customfield']];
        yield 'ConfirmationTag' => [ConfirmationTag::fromArray(...), ['customfield']];
        yield 'Contact' => [Contact::fromArray(...), ['updated', 'customfield']];
        yield 'CreditNote' => [CreditNote::fromArray(...), ['updated', 'customfield']];
        yield 'CreditNoteComment' => [CreditNoteComment::fromArray(...), ['customfield']];
        yield 'CreditNoteItem' => [CreditNoteItem::fromArray(...), ['customfield']];
        yield 'CreditNotePayment' => [CreditNotePayment::fromArray(...), ['customfield']];
        yield 'CreditNoteTag' => [CreditNoteTag::fromArray(...), ['customfield']];
        yield 'DeliveryNote' => [DeliveryNote::fromArray(...), ['updated', 'customfield']];
        yield 'DeliveryNoteComment' => [DeliveryNoteComment::fromArray(...), ['customfield']];
        yield 'DeliveryNoteItem' => [DeliveryNoteItem::fromArray(...), ['customfield']];
        yield 'DeliveryNoteTag' => [DeliveryNoteTag::fromArray(...), ['customfield']];
        yield 'EmailTemplate' => [EmailTemplate::fromArray(...), ['created', 'updated']];
        yield 'FreeText' => [FreeText::fromArray(...), ['created', 'updated']];
        yield 'InboxDocument' => [InboxDocument::fromArray(...), ['updated', 'customfield']];
        yield 'Incoming' => [Incoming::fromArray(...), ['updated', 'customfield']];
        yield 'IncomingComment' => [IncomingComment::fromArray(...), ['customfield']];
        yield 'IncomingPayment' => [IncomingPayment::fromArray(...), ['customfield']];
        yield 'IncomingProperty' => [IncomingProperty::fromArray(...), ['customfield']];
        yield 'IncomingPropertyValue' => [IncomingPropertyValue::fromArray(...), ['created', 'updated', 'customfield']];
        yield 'IncomingTag' => [IncomingTag::fromArray(...), ['customfield']];
        yield 'Invoice' => [Invoice::fromArray(...), ['updated', 'customfield']];
        yield 'InvoiceComment' => [InvoiceComment::fromArray(...), ['customfield']];
        yield 'InvoiceItem' => [InvoiceItem::fromArray(...), ['customfield']];
        yield 'InvoicePayment' => [InvoicePayment::fromArray(...), ['customfield']];
        yield 'InvoiceTag' => [InvoiceTag::fromArray(...), ['customfield']];
        yield 'Letter' => [Letter::fromArray(...), ['updated', 'customfield']];
        yield 'LetterComment' => [LetterComment::fromArray(...), ['customfield']];
        yield 'LetterTag' => [LetterTag::fromArray(...), ['customfield']];
        yield 'Offer' => [Offer::fromArray(...), ['updated', 'customfield']];
        yield 'OfferComment' => [OfferComment::fromArray(...), ['customfield']];
        yield 'OfferItem' => [OfferItem::fromArray(...), ['customfield']];
        yield 'OfferTag' => [OfferTag::fromArray(...), ['customfield']];
        yield 'Recurring' => [Recurring::fromArray(...), ['updated', 'customfield']];
        yield 'RecurringItem' => [RecurringItem::fromArray(...), ['customfield']];
        yield 'RecurringTag' => [RecurringTag::fromArray(...), ['customfield']];
        yield 'Reminder' => [Reminder::fromArray(...), ['updated', 'customfield']];
        yield 'ReminderItem' => [ReminderItem::fromArray(...), ['customfield']];
        yield 'ReminderTag' => [ReminderTag::fromArray(...), ['customfield']];
        yield 'ReminderText' => [ReminderText::fromArray(...), ['customfield']];
        yield 'Supplier' => [Supplier::fromArray(...), ['updated', 'customfield']];
        yield 'SupplierProperty' => [SupplierProperty::fromArray(...), ['customfield']];
        yield 'SupplierPropertyValue' => [SupplierPropertyValue::fromArray(...), ['created', 'updated', 'customfield']];
        yield 'SupplierTag' => [SupplierTag::fromArray(...), ['customfield']];
        yield 'TaxRate' => [TaxRate::fromArray(...), ['created', 'updated', 'customfield']];
        yield 'Template' => [Template::fromArray(...), ['updated', 'customfield']];
        yield 'Unit' => [Unit::fromArray(...), ['created', 'updated', 'customfield']];
        yield 'User' => [User::fromArray(...), ['created', 'updated', 'customfield']];
        yield 'UserProperty' => [UserProperty::fromArray(...), ['customfield']];
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param list<string>                         $keys
     */
    #[Test]
    #[DataProvider('models')]
    public function itHydratesAndExportsMetadataFields(Closure $hydrate, array $keys): void
    {
        $data = [
            'id' => '1',
            'created' => '2026-10-20T19:58:59+02:00',
            'updated' => '2026-10-20T20:02:16+02:00',
            'customfield' => 'crm-4711',
        ];

        $model = $hydrate($data);
        $props = get_object_vars($model);
        self::assertTrue(method_exists($model, 'toArray'));
        $array = $model->toArray();
        self::assertIsArray($array);

        foreach ($keys as $key) {
            self::assertSame($data[$key], $array[$key]);

            if ('customfield' === $key) {
                self::assertSame('crm-4711', $props['customfield']);

                continue;
            }

            self::assertInstanceOf(DateTimeImmutable::class, $props[$key]);
        }
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param list<string>                         $keys
     */
    #[Test]
    #[DataProvider('models')]
    public function itTreatsEmptyMetadataAsNull(Closure $hydrate, array $keys): void
    {
        $props = get_object_vars($hydrate(['id' => '1', 'created' => '', 'updated' => '', 'customfield' => '']));

        foreach ($keys as $key) {
            self::assertNull($props[$key]);
        }
    }
}
