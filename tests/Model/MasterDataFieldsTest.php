<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Closure;
use DateTimeImmutable;
use Generator;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Article;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\Contact;
use Justpilot\Billomat\Model\Country;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\CreditNotePayment;
use Justpilot\Billomat\Model\Currency;
use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;
use Justpilot\Billomat\Model\Enum\FreeTextType;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateType;
use Justpilot\Billomat\Model\FreeText;
use Justpilot\Billomat\Model\InboxDocument;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Model\IncomingPayment;
use Justpilot\Billomat\Model\InvoiceComment;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Model\LetterComment;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\OfferComment;
use Justpilot\Billomat\Model\Supplier;
use Justpilot\Billomat\Model\TaxRate;
use Justpilot\Billomat\Model\Template;
use Justpilot\Billomat\Model\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Neue Felder an Stammdaten und Nebenressourcen aus den Billomat-Antworten (Stand 2026-10): Artikel, Kunden, Lieferanten, Eingangsrechnungen, Kontakte, Zahlungen, Kommentare, Inbox, Benutzer, Einstellungen.
 */
#[CoversClass(Article::class)]
#[CoversClass(Client::class)]
#[CoversClass(ConfirmationComment::class)]
#[CoversClass(Contact::class)]
#[CoversClass(Country::class)]
#[CoversClass(CreditNoteComment::class)]
#[CoversClass(CreditNotePayment::class)]
#[CoversClass(Currency::class)]
#[CoversClass(DeliveryNoteComment::class)]
#[CoversClass(FreeText::class)]
#[CoversClass(InboxDocument::class)]
#[CoversClass(Incoming::class)]
#[CoversClass(IncomingPayment::class)]
#[CoversClass(InvoiceComment::class)]
#[CoversClass(InvoicePayment::class)]
#[CoversClass(LetterComment::class)]
#[CoversClass(OfferComment::class)]
#[CoversClass(Supplier::class)]
#[CoversClass(TaxRate::class)]
#[CoversClass(Template::class)]
#[CoversClass(User::class)]
#[UsesClass(ItemType::class)]
#[UsesClass(ConfirmationCommentActionKey::class)]
#[UsesClass(CreditNoteCommentActionKey::class)]
#[UsesClass(DeliveryNoteCommentActionKey::class)]
#[UsesClass(IncomingStatus::class)]
#[UsesClass(InvoiceCommentActionKey::class)]
#[UsesClass(PaymentType::class)]
#[UsesClass(LetterCommentActionKey::class)]
#[UsesClass(NetGross::class)]
#[UsesClass(OfferCommentActionKey::class)]
#[UsesClass(TemplateDocumentType::class)]
#[UsesClass(TemplateFormat::class)]
#[UsesClass(TemplateType::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
#[UsesClass(ListCaster::class)]
final class MasterDataFieldsTest extends TestCase
{
    /**
     * Pro Model: `fromArray()`, Rohdaten wie von Billomat (alles Strings) und
     * die erwarteten Property-Werte. Datumswerte werden als `Y-m-d` verglichen.
     *
     * @return Generator<string, array{Closure(array<string,mixed>): object, array<string,mixed>, array<string,mixed>}>
     */
    public static function models(): Generator
    {
        yield 'Article' => [
            Article::fromArray(...),
            ['id' => '1', 'archived' => '1', 'type' => 'SERVICE', 'revenue_account_number' => 'Text', 'cost_center' => 'Text', 'purchase_price_net_gross' => 'NET'],
            ['archived' => true, 'type' => ItemType::SERVICE, 'revenueAccountNumber' => 'Text', 'costCenter' => 'Text', 'purchasePriceNetGross' => NetGross::NET],
        ];
        yield 'Client' => [
            Client::fromArray(...),
            ['id' => '1', 'dig_exclude' => '1'],
            ['digExclude' => true],
        ];
        yield 'ConfirmationComment' => [
            ConfirmationComment::fromArray(...),
            ['id' => '1', 'public' => '1', 'by_client' => '1', 'email_id' => '42', 'client_id' => '42'],
            ['public' => true, 'byClient' => true, 'emailId' => 42, 'clientId' => 42],
        ];
        yield 'Contact' => [
            Contact::fromArray(...),
            ['id' => '1', 'name' => 'Text', 'address' => 'Text', 'www' => 'Text', 'locale' => 'Text'],
            ['name' => 'Text', 'address' => 'Text', 'www' => 'Text', 'locale' => 'Text'],
        ];
        yield 'Country' => [
            Country::fromArray(...),
            ['id' => '42'],
            ['id' => 42],
        ];
        yield 'CreditNoteComment' => [
            CreditNoteComment::fromArray(...),
            ['id' => '1', 'public' => '1', 'by_client' => '1', 'email_id' => '42', 'client_id' => '42'],
            ['public' => true, 'byClient' => true, 'emailId' => 42, 'clientId' => 42],
        ];
        yield 'CreditNotePayment' => [
            CreditNotePayment::fromArray(...),
            ['id' => '1', 'currency_code' => 'Text', 'quote' => '12.5', 'payment_group' => 'Text', 'finapi_transaction_id' => 'Text', 'finapi_transaction_amount' => '12.5', 'document_title' => 'Text'],
            ['currencyCode' => 'Text', 'quote' => 12.5, 'paymentGroup' => 'Text', 'finapiTransactionId' => 'Text', 'finapiTransactionAmount' => '12.5', 'documentTitle' => 'Text'],
        ];
        yield 'Currency' => [
            Currency::fromArray(...),
            ['id' => '42'],
            ['id' => 42],
        ];
        yield 'DeliveryNoteComment' => [
            DeliveryNoteComment::fromArray(...),
            ['id' => '1', 'public' => '1', 'by_client' => '1', 'email_id' => '42', 'client_id' => '42'],
            ['public' => true, 'byClient' => true, 'emailId' => 42, 'clientId' => 42],
        ];
        yield 'FreeText' => [
            FreeText::fromArray(...),
            ['id' => '1', 'type' => 'CORRECTION', 'name' => 'Text', 'is_default' => '1'],
            ['type' => FreeTextType::CORRECTION, 'name' => 'Text', 'isDefault' => true],
        ];
        yield 'InboxDocument' => [
            InboxDocument::fromArray(...),
            ['id' => '1', 'user_id' => '42', 'document_type' => 'Text', 'page_count' => '42', 'file_url' => 'Text', 'metadata' => 'Text'],
            ['userId' => 42, 'documentType' => 'Text', 'pageCount' => 42, 'fileUrl' => 'Text', 'metadata' => 'Text'],
        ];
        yield 'Incoming' => [
            Incoming::fromArray(...),
            ['id' => '1', 'client_number' => 'Text', 'expense_account_number' => 'Text', 'category' => 'Text', 'page_count' => '42', 'has_attachment' => '1'],
            ['clientNumber' => 'Text', 'expenseAccountNumber' => 'Text', 'category' => 'Text', 'pageCount' => 42, 'hasAttachment' => true],
        ];
        yield 'IncomingPayment' => [
            IncomingPayment::fromArray(...),
            ['id' => '1', 'currency_code' => 'Text', 'quote' => '12.5', 'payment_group' => 'Text', 'finapi_transaction_id' => 'Text', 'finapi_transaction_amount' => '12.5', 'document_title' => 'Text'],
            ['currencyCode' => 'Text', 'quote' => 12.5, 'paymentGroup' => 'Text', 'finapiTransactionId' => 'Text', 'finapiTransactionAmount' => '12.5', 'documentTitle' => 'Text'],
        ];
        yield 'InvoiceComment' => [
            InvoiceComment::fromArray(...),
            ['id' => '1', 'public' => '1', 'by_client' => '1', 'email_id' => '42', 'client_id' => '42'],
            ['public' => true, 'byClient' => true, 'emailId' => 42, 'clientId' => 42],
        ];
        yield 'InvoicePayment' => [
            InvoicePayment::fromArray(...),
            ['id' => '1', 'currency_code' => 'Text', 'quote' => '12.5', 'payment_group' => 'Text', 'finapi_transaction_id' => 'Text', 'finapi_transaction_amount' => '12.5', 'document_title' => 'Text'],
            ['currencyCode' => 'Text', 'quote' => 12.5, 'paymentGroup' => 'Text', 'finapiTransactionId' => 'Text', 'finapiTransactionAmount' => '12.5', 'documentTitle' => 'Text'],
        ];
        yield 'LetterComment' => [
            LetterComment::fromArray(...),
            ['id' => '1', 'public' => '1', 'by_client' => '1', 'email_id' => '42', 'client_id' => '42'],
            ['public' => true, 'byClient' => true, 'emailId' => 42, 'clientId' => 42],
        ];
        yield 'OfferComment' => [
            OfferComment::fromArray(...),
            ['id' => '1', 'public' => '1', 'by_client' => '1', 'email_id' => '42', 'client_id' => '42'],
            ['public' => true, 'byClient' => true, 'emailId' => 42, 'clientId' => 42],
        ];
        yield 'Supplier' => [
            Supplier::fromArray(...),
            ['id' => '1', 'is_eu_country' => '1', 'locale' => 'Text', 'creditor_account_number' => 'Text', 'creditor_identifier' => 'Text', 'costs_gross' => '12.5', 'costs_net' => '12.5'],
            ['isEuCountry' => true, 'locale' => 'Text', 'creditorAccountNumber' => 'Text', 'creditorIdentifier' => 'Text', 'costsGross' => '12.5', 'costsNet' => '12.5'],
        ];
        yield 'TaxRate' => [
            TaxRate::fromArray(...),
            ['id' => '1', 'can_delete' => '1'],
            ['canDelete' => true],
        ];
        yield 'Template' => [
            Template::fromArray(...),
            ['id' => '1', 'is_background_available' => '1'],
            ['isBackgroundAvailable' => true],
        ];
        yield 'User' => [
            User::fromArray(...),
            ['id' => '1', 'locale' => 'Text', 'timezone' => 'Text', 'is_account_owner' => '1'],
            ['locale' => 'Text', 'timezone' => 'Text', 'isAccountOwner' => true],
        ];
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param array<string,mixed>                  $data
     * @param array<string,mixed>                  $expected
     */
    #[Test]
    #[DataProvider('models')]
    public function itHydratesNewFields(Closure $hydrate, array $data, array $expected): void
    {
        $props = get_object_vars($hydrate($data));

        foreach ($expected as $property => $value) {
            $actual = $props[$property];
            if ($actual instanceof DateTimeImmutable) {
                $actual = $actual->format('Y-m-d');
            }
            if ($actual instanceof Money) {
                $actual = $actual->toString();
            }

            self::assertSame($value, $actual, $property);
        }
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param array<string,mixed>                  $data
     * @param array<string,mixed>                  $expected
     */
    #[Test]
    #[DataProvider('models')]
    public function itExportsNewFieldsInToArray(Closure $hydrate, array $data, array $expected): void
    {
        $model = $hydrate($data);
        self::assertTrue(method_exists($model, 'toArray'));
        $array = $model->toArray();
        self::assertIsArray($array);

        foreach (array_keys($data) as $key) {
            self::assertArrayHasKey($key, $array);
        }
    }

    /**
     * @param Closure(array<string,mixed>): object $hydrate
     * @param array<string,mixed>                  $data
     * @param array<string,mixed>                  $expected
     */
    #[Test]
    #[DataProvider('models')]
    public function itTreatsEmptyValuesAsNull(Closure $hydrate, array $data, array $expected): void
    {
        $empty = array_map(static fn (): string => '', $data);
        $props = get_object_vars($hydrate($empty));

        foreach (array_keys($expected) as $property) {
            self::assertNull($props[$property], $property);
        }
    }
}
