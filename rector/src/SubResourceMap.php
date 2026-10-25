<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Rector;

/**
 * Zuordnung der flachen 3.x-Properties von `BillomatClient` zu ihren 4.0-Pendants.
 *
 * Eine Zeile pro 3.x-Property: alte Klasse, Parent-Property und -Klasse auf `BillomatClient`,
 * Child-Property am Parent (`null`, wenn die Property direkt umbenannt wurde) und die 4.0-Klasse
 * des Childs. Die Regeln lesen nur diese Tabelle; Klassennamen stehen als Strings, weil die
 * 3.x-Klassen in 4.0 nicht mehr existieren. Nur für die Upgrade-Regeln gedacht, nicht Teil der
 * öffentlichen API des SDKs.
 */
final class SubResourceMap
{
    private const string API = 'Justpilot\Billomat\Api\\';

    /**
     * Generische Klassen, deren `listBy{Parent}()` zu `list()` und deren Parent-ID-Parameter zu `$parentId` wurde.
     */
    private const array GENERIC_LIST_BY_PARENT = ['CommentsApi', 'TagsApi', 'ItemsApi'];

    /**
     * Klassen, die ihren Namen behalten, deren `listBy{Parent}()` aber `list()` heißt (Parameternamen unverändert).
     */
    private const array KEPT_LIST_BY_PARENT = ['ReminderItemsApi', 'RecurringEmailReceiversApi'];

    /**
     * Klassen, deren Listen-Methoden die Parent-ID als neuen optionalen ersten Parameter bekommen haben.
     */
    private const array OPTIONAL_PARENT = ['PaymentsApi', 'PropertyValuesApi'];

    /**
     * 3.x-Property => [3.x-Klasse, Parent-Property, Parent-Klasse, Child-Property|null, 4.0-Klasse].
     *
     * @var array<string, array{string, string, string, ?string, string}>
     */
    private const array ENTRIES = [
        'invoiceItems' => ['InvoiceItemsApi', 'invoices', 'InvoicesApi', 'items', 'ItemsApi'],
        'invoicePayments' => ['InvoicePaymentsApi', 'invoices', 'InvoicesApi', 'payments', 'PaymentsApi'],
        'invoiceComments' => ['InvoiceCommentsApi', 'invoices', 'InvoicesApi', 'comments', 'CommentsApi'],
        'invoiceTags' => ['InvoiceTagsApi', 'invoices', 'InvoicesApi', 'tags', 'TagsApi'],
        'offerItems' => ['OfferItemsApi', 'offers', 'OffersApi', 'items', 'ItemsApi'],
        'offerComments' => ['OfferCommentsApi', 'offers', 'OffersApi', 'comments', 'CommentsApi'],
        'offerTags' => ['OfferTagsApi', 'offers', 'OffersApi', 'tags', 'TagsApi'],
        'confirmationItems' => ['ConfirmationItemsApi', 'confirmations', 'ConfirmationsApi', 'items', 'ItemsApi'],
        'confirmationComments' => ['ConfirmationCommentsApi', 'confirmations', 'ConfirmationsApi', 'comments', 'CommentsApi'],
        'confirmationTags' => ['ConfirmationTagsApi', 'confirmations', 'ConfirmationsApi', 'tags', 'TagsApi'],
        'creditNoteItems' => ['CreditNoteItemsApi', 'creditNotes', 'CreditNotesApi', 'items', 'ItemsApi'],
        'creditNotePayments' => ['CreditNotePaymentsApi', 'creditNotes', 'CreditNotesApi', 'payments', 'PaymentsApi'],
        'creditNoteComments' => ['CreditNoteCommentsApi', 'creditNotes', 'CreditNotesApi', 'comments', 'CommentsApi'],
        'creditNoteTags' => ['CreditNoteTagsApi', 'creditNotes', 'CreditNotesApi', 'tags', 'TagsApi'],
        'deliveryNoteItems' => ['DeliveryNoteItemsApi', 'deliveryNotes', 'DeliveryNotesApi', 'items', 'ItemsApi'],
        'deliveryNoteComments' => ['DeliveryNoteCommentsApi', 'deliveryNotes', 'DeliveryNotesApi', 'comments', 'CommentsApi'],
        'deliveryNoteTags' => ['DeliveryNoteTagsApi', 'deliveryNotes', 'DeliveryNotesApi', 'tags', 'TagsApi'],
        'recurringItems' => ['RecurringItemsApi', 'recurrings', 'RecurringsApi', 'items', 'ItemsApi'],
        'recurringTags' => ['RecurringTagsApi', 'recurrings', 'RecurringsApi', 'tags', 'TagsApi'],
        'recurringEmailReceivers' => ['RecurringEmailReceiversApi', 'recurrings', 'RecurringsApi', 'emailReceivers', 'RecurringEmailReceiversApi'],
        'reminderItems' => ['ReminderItemsApi', 'reminders', 'RemindersApi', 'items', 'ReminderItemsApi'],
        'reminderTags' => ['ReminderTagsApi', 'reminders', 'RemindersApi', 'tags', 'TagsApi'],
        'letterComments' => ['LetterCommentsApi', 'letters', 'LettersApi', 'comments', 'CommentsApi'],
        'letterTags' => ['LetterTagsApi', 'letters', 'LettersApi', 'tags', 'TagsApi'],
        'incomingComments' => ['IncomingCommentsApi', 'incomings', 'IncomingsApi', 'comments', 'CommentsApi'],
        'incomingPayments' => ['IncomingPaymentsApi', 'incomings', 'IncomingsApi', 'payments', 'PaymentsApi'],
        'incomingTags' => ['IncomingTagsApi', 'incomings', 'IncomingsApi', 'tags', 'TagsApi'],
        'incomingProperties' => ['IncomingPropertiesApi', 'incomings', 'IncomingsApi', 'properties', 'PropertiesApi'],
        'incomingPropertyValues' => ['IncomingPropertyValuesApi', 'incomings', 'IncomingsApi', 'propertyValues', 'PropertyValuesApi'],
        'articleTags' => ['ArticleTagsApi', 'articles', 'ArticlesApi', 'tags', 'TagsApi'],
        'articleProperties' => ['ArticlePropertiesApi', 'articles', 'ArticlesApi', 'properties', 'PropertiesApi'],
        'articlePropertyValues' => ['ArticlePropertyValuesApi', 'articles', 'ArticlesApi', 'propertyValues', 'PropertyValuesApi'],
        'clientTags' => ['ClientTagsApi', 'clients', 'ClientsApi', 'tags', 'TagsApi'],
        'clientProperties' => ['ClientPropertiesApi', 'clients', 'ClientsApi', 'properties', 'PropertiesApi'],
        'clientPropertyValues' => ['ClientPropertyValuesApi', 'clients', 'ClientsApi', 'propertyValues', 'PropertyValuesApi'],
        'supplierTags' => ['SupplierTagsApi', 'suppliers', 'SuppliersApi', 'tags', 'TagsApi'],
        'supplierProperties' => ['SupplierPropertiesApi', 'suppliers', 'SuppliersApi', 'properties', 'PropertiesApi'],
        'supplierPropertyValues' => ['SupplierPropertyValuesApi', 'suppliers', 'SuppliersApi', 'propertyValues', 'PropertyValuesApi'],
        'userProperties' => ['UserPropertiesApi', 'users', 'UsersApi', 'properties', 'PropertiesApi'],
        'dunningLevels' => ['DunningLevelsApi', 'reminderTexts', 'ReminderTextsApi', null, 'ReminderTextsApi'],
    ];

    /**
     * Ziel einer flachen 3.x-Property: `[Parent-Property, Child-Property|null]`.
     *
     * @return array{string, ?string}|null
     */
    public static function target(string $oldProperty): ?array
    {
        $entry = self::ENTRIES[$oldProperty] ?? null;

        return null === $entry ? null : [$entry[1], $entry[3]];
    }

    /**
     * Zu einer entfernten 3.x-Klasse: `[Parent-Klasse (FQCN), Child-Property]`, mit der `new Old($http)`
     * zu `new Parent($http)->child` wird. `null` für Klassen, die in 4.0 noch existieren.
     *
     * @return array{class-string, string}|null
     */
    public static function construction(string $oldClass): ?array
    {
        foreach (self::ENTRIES as $entry) {
            if (self::API.$entry[0] === $oldClass && $entry[0] !== $entry[4] && null !== $entry[3]) {
                /** @var class-string $parent */
                $parent = self::API.$entry[2];

                return [$parent, $entry[3]];
            }
        }

        return null;
    }

    /**
     * 4.0-Klasse (FQCN) zu einer 3.x-Property oder einer 3.x-Klasse (FQCN).
     */
    public static function newClassFor(?string $oldProperty, ?string $oldClass): ?string
    {
        if (null !== $oldProperty && isset(self::ENTRIES[$oldProperty])) {
            return self::API.self::ENTRIES[$oldProperty][4];
        }

        foreach (self::ENTRIES as $entry) {
            if (self::API.$entry[0] === $oldClass) {
                return self::API.$entry[4];
            }
        }

        return null;
    }

    /**
     * Umbenennungen für `RenameClassRector`: entfernte 3.x-Klasse => generische 4.0-Klasse.
     *
     * @return array<string, string>
     */
    public static function classRenames(): array
    {
        $renames = [];
        foreach (self::ENTRIES as $entry) {
            if ($entry[0] !== $entry[4]) {
                $renames[self::API.$entry[0]] = self::API.$entry[4];
            }
        }

        return $renames;
    }

    /**
     * `listBy{Parent}()` heißt in 4.0 `list()`.
     */
    public static function hasListByParent(string $newClass): bool
    {
        return \in_array(self::shortName($newClass), [...self::GENERIC_LIST_BY_PARENT, ...self::KEPT_LIST_BY_PARENT], true);
    }

    /**
     * Der Parent-ID-Parameter (`$invoiceId`, `$offerId`, …) heißt in 4.0 `$parentId`.
     */
    public static function hasGenericParentId(string $newClass): bool
    {
        return \in_array(self::shortName($newClass), self::GENERIC_LIST_BY_PARENT, true);
    }

    /**
     * `list()`, `listPage()` und `iterateAll()` haben die Parent-ID als neuen optionalen ersten Parameter.
     */
    public static function hasOptionalParent(string $newClass): bool
    {
        return \in_array(self::shortName($newClass), self::OPTIONAL_PARENT, true);
    }

    private static function shortName(string $class): string
    {
        return substr($class, \strlen(self::API));
    }
}
