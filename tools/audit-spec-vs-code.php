<?php

declare(strict_types=1);

/*
 * Vergleicht docs/spec/billomat.json gegen src/Api/ + src/Model/Enum/ + docs/resources/
 * und schreibt eine deterministische Lücken-Übersicht nach docs/audit.md.
 *
 * Aufruf:
 *   composer audit:spec
 *
 * Es werden keine Quell-Dateien verändert; nur docs/audit.md wird neu geschrieben.
 */

namespace Justpilot\Billomat\Tools;

use ReflectionClass;
use ReflectionEnum;
use RuntimeException;

require __DIR__ . '/../vendor/autoload.php';

const PROJECT_ROOT = __DIR__ . '/..';
const SPEC_PATH = PROJECT_ROOT . '/docs/spec/billomat.json';
const AUDIT_PATH = PROJECT_ROOT . '/docs/audit.md';
const DOCS_RESOURCES_DIR = PROJECT_ROOT . '/docs/resources';
const ENUMS_NAMESPACE = 'Justpilot\\Billomat\\Model\\Enum\\';
const API_NAMESPACE = 'Justpilot\\Billomat\\Api\\';

/**
 * Zuordnung: Billomat-Slug (deutsch, aus URL) → erwartete SDK-Klassen-Stämme.
 * Pro Slug entweder:
 *   - Array mit api/model/createOptions/updateOptions/docFile
 *   - null  → bewusst nicht abzubilden (z.B. Konzept-Doku unter grundlagen/)
 *
 * @var array<string, array{
 *   api?: string,
 *   model?: string,
 *   createOptions?: string,
 *   updateOptions?: string,
 *   docFile?: string,
 *   conceptFile?: string,
 *   note?: string
 * }|null>
 */
$mapping = [
    'kunden' => ['api' => 'ClientsApi', 'model' => 'Client',
        'createOptions' => 'ClientCreateOptions', 'updateOptions' => 'ClientUpdateOptions',
        'docFile' => 'clients.md'],
    'kunden/attribute' => ['api' => 'ClientPropertiesApi', 'model' => 'ClientProperty',
        'docFile' => 'properties.md',
        'note' => 'Geteilte Doku unter properties.md.'],
    'kunden/kontakte' => ['api' => 'ContactsApi', 'model' => 'Contact',
        'createOptions' => 'ContactCreateOptions', 'updateOptions' => 'ContactUpdateOptions',
        'docFile' => 'contacts.md'],
    'kunden/schlagworte' => ['api' => 'ClientTagsApi', 'model' => 'ClientTag',
        'createOptions' => 'ClientTagCreateOptions', 'docFile' => 'client-tags.md'],

    'rechnungen' => ['api' => 'InvoicesApi', 'model' => 'Invoice',
        'createOptions' => 'InvoiceCreateOptions', 'updateOptions' => 'InvoiceUpdateOptions',
        'docFile' => 'invoices.md'],
    'rechnungen/positionen' => ['api' => 'InvoiceItemsApi', 'model' => 'InvoiceItem',
        'createOptions' => 'InvoiceItemCreateOptions', 'docFile' => 'invoice-items.md'],
    'rechnungen/zahlungen' => ['api' => 'InvoicePaymentsApi', 'model' => 'InvoicePayment',
        'createOptions' => 'InvoicePaymentCreateOptions', 'docFile' => 'invoice-payments.md'],
    'rechnungen/kommentare' => ['api' => 'InvoiceCommentsApi', 'model' => 'InvoiceComment',
        'createOptions' => 'InvoiceCommentCreateOptions', 'docFile' => 'invoice-comments.md'],
    'rechnungen/schlagworte' => ['api' => 'InvoiceTagsApi', 'model' => 'InvoiceTag',
        'createOptions' => 'InvoiceTagCreateOptions', 'docFile' => 'invoice-tags.md'],

    'angebote' => ['api' => 'OffersApi', 'model' => 'Offer',
        'createOptions' => 'OfferCreateOptions', 'updateOptions' => 'OfferUpdateOptions',
        'docFile' => 'offers.md'],
    'angebote/positionen' => ['api' => 'OfferItemsApi', 'model' => 'OfferItem',
        'createOptions' => 'OfferItemCreateOptions', 'docFile' => 'offers.md'],
    'angebote/kommentare' => ['api' => 'OfferCommentsApi', 'model' => 'OfferComment',
        'createOptions' => 'OfferCommentCreateOptions', 'docFile' => 'offers.md'],
    'angebote/schlagworte' => ['api' => 'OfferTagsApi', 'model' => 'OfferTag',
        'createOptions' => 'OfferTagCreateOptions', 'docFile' => 'offers.md'],

    'auftragsbestaetigungen' => ['api' => 'ConfirmationsApi', 'model' => 'Confirmation',
        'createOptions' => 'ConfirmationCreateOptions', 'updateOptions' => 'ConfirmationUpdateOptions',
        'docFile' => 'confirmations.md'],
    'lieferscheine' => ['api' => 'DeliveryNotesApi', 'model' => 'DeliveryNote',
        'createOptions' => 'DeliveryNoteCreateOptions', 'updateOptions' => 'DeliveryNoteUpdateOptions',
        'docFile' => 'delivery-notes.md'],

    'gutschriften' => ['api' => 'CreditNotesApi', 'model' => 'CreditNote',
        'createOptions' => 'CreditNoteCreateOptions', 'updateOptions' => 'CreditNoteUpdateOptions',
        'docFile' => 'credit-notes.md'],
    'gutschriften/positionen' => ['api' => 'CreditNoteItemsApi', 'model' => 'CreditNoteItem',
        'createOptions' => 'CreditNoteItemCreateOptions', 'docFile' => 'credit-notes.md'],
    'gutschriften/kommentare' => ['api' => 'CreditNoteCommentsApi', 'model' => 'CreditNoteComment',
        'createOptions' => 'CreditNoteCommentCreateOptions', 'docFile' => 'credit-notes.md'],
    'gutschriften/schlagworte' => ['api' => 'CreditNoteTagsApi', 'model' => 'CreditNoteTag',
        'createOptions' => 'CreditNoteTagCreateOptions', 'docFile' => 'credit-notes.md'],
    'gutschriften/zahlungen' => ['api' => 'CreditNotePaymentsApi', 'model' => 'CreditNotePayment',
        'createOptions' => 'CreditNotePaymentCreateOptions', 'docFile' => 'credit-notes.md'],

    'mahnungen' => ['api' => 'RemindersApi', 'model' => 'Reminder',
        'createOptions' => 'ReminderCreateOptions', 'updateOptions' => 'ReminderUpdateOptions',
        'docFile' => 'reminders.md'],
    'mahnungen/positionen' => ['api' => 'ReminderItemsApi', 'docFile' => 'reminders.md'],
    'mahnungen/schlagworte' => ['api' => 'ReminderTagsApi', 'model' => 'ReminderTag',
        'createOptions' => 'ReminderTagCreateOptions', 'docFile' => 'reminders.md'],

    'briefe' => ['api' => 'LettersApi', 'model' => 'Letter',
        'createOptions' => 'LetterCreateOptions', 'updateOptions' => 'LetterUpdateOptions',
        'docFile' => 'letters.md'],

    'artikel' => ['api' => 'ArticlesApi', 'model' => 'Article',
        'createOptions' => 'ArticleCreateOptions', 'updateOptions' => 'ArticleUpdateOptions',
        'docFile' => 'articles.md'],
    'artikel/attribute' => ['api' => 'ArticlePropertyValuesApi', 'docFile' => 'articles.md'],
    'artikel/schlagworte' => ['api' => 'ArticleTagsApi', 'model' => 'ArticleTag',
        'createOptions' => 'ArticleTagCreateOptions', 'docFile' => 'articles.md'],

    'lieferanten' => ['api' => 'SuppliersApi', 'model' => 'Supplier',
        'createOptions' => 'SupplierCreateOptions', 'updateOptions' => 'SupplierUpdateOptions',
        'docFile' => 'suppliers.md'],
    'lieferanten/attribute' => ['api' => 'SupplierPropertyValuesApi', 'docFile' => 'suppliers.md'],
    'lieferanten/schlagworte' => ['api' => 'SupplierTagsApi', 'model' => 'SupplierTag',
        'createOptions' => 'SupplierTagCreateOptions', 'docFile' => 'suppliers.md'],

    'eingangsrechnungen' => ['api' => 'IncomingsApi', 'model' => 'Incoming',
        'createOptions' => 'IncomingCreateOptions', 'updateOptions' => 'IncomingUpdateOptions',
        'docFile' => 'incomings.md'],
    'eingangsrechnungen/inbox' => ['api' => 'InboxDocumentsApi', 'model' => 'InboxDocument',
        'createOptions' => 'InboxDocumentCreateOptions', 'docFile' => 'inbox-documents.md'],
    'eingangsrechnungen/attribute' => ['api' => 'IncomingPropertyValuesApi', 'docFile' => 'incomings.md'],
    'eingangsrechnungen/kommentare' => ['api' => 'IncomingCommentsApi', 'model' => 'IncomingComment',
        'createOptions' => 'IncomingCommentCreateOptions', 'docFile' => 'incomings.md'],
    'eingangsrechnungen/posten' => ['docFile' => 'incomings.md',
        'note' => 'Posten/Items werden inline über Incoming verwaltet.'],
    'eingangsrechnungen/schlagworte' => ['api' => 'IncomingTagsApi', 'model' => 'IncomingTag',
        'createOptions' => 'IncomingTagCreateOptions', 'docFile' => 'incomings.md'],
    'eingangsrechnungen/kategorien' => ['api' => 'IncomingCategoriesApi', 'model' => 'IncomingCategory',
        'docFile' => 'incoming-categories.md',
        'note' => 'Read-only; Mutations-Endpunkte sind in der Spec nicht dokumentiert.'],
    'eingangsrechnungen/zahlungen' => ['api' => 'IncomingPaymentsApi', 'model' => 'IncomingPayment',
        'createOptions' => 'IncomingPaymentCreateOptions', 'docFile' => 'incomings.md'],

    'abo-rechnungen' => ['api' => 'RecurringsApi', 'model' => 'Recurring',
        'createOptions' => 'RecurringCreateOptions', 'updateOptions' => 'RecurringUpdateOptions',
        'docFile' => 'recurrings.md'],
    'abo-rechnungen/positionen' => ['api' => 'RecurringItemsApi', 'docFile' => 'recurrings.md'],
    'abo-rechnungen/empfaenger' => ['api' => 'RecurringEmailReceiversApi', 'docFile' => 'recurrings.md'],
    'abo-rechnungen/schlagworte' => ['api' => 'RecurringTagsApi', 'docFile' => 'recurrings.md'],

    'benutzer' => ['api' => 'UsersApi', 'model' => 'User', 'docFile' => 'users.md'],
    'laender' => ['api' => 'CountriesApi', 'model' => 'Country', 'docFile' => 'countries.md'],
    'waehrungen' => ['api' => 'CurrenciesApi', 'model' => 'Currency', 'docFile' => 'currencies.md'],
    'webhooks' => ['conceptFile' => 'webhooks.md',
        'note' => 'Empfänger-seitiges Konzept – kein REST-Endpunkt; siehe docs/concepts/webhooks.md.'],
    'account' => ['api' => 'AccountApi', 'model' => 'Account', 'docFile' => 'account.md'],
    'aktivitaeten' => ['api' => 'ActivitiesApi', 'model' => 'Activity', 'docFile' => 'activities.md'],
    'suche' => ['api' => 'SearchApi', 'model' => 'SearchResult', 'docFile' => 'search.md'],

    'einstellungen' => ['api' => 'SettingsApi', 'docFile' => 'settings.md'],
    'einstellungen/steuersaetze' => ['api' => 'TaxesApi', 'docFile' => 'taxes.md'],
    'einstellungen/steuerfreie-laender' => ['api' => 'CountryTaxesApi', 'model' => 'CountryTax',
        'docFile' => 'settings-tax-free-countries.md',
        'note' => 'Read-only im SDK; Spec dokumentiert zusätzlich POST/PUT/DELETE auf /country-taxes.'],
    'einstellungen/einheiten' => ['api' => 'UnitsApi', 'model' => 'Unit',
        'docFile' => 'settings-units.md'],
    'einstellungen/email-vorlagen' => ['api' => 'EmailTemplatesApi', 'model' => 'EmailTemplate',
        'docFile' => 'settings-email-templates.md'],
    'einstellungen/freitexte' => ['api' => 'FreeTextsApi', 'model' => 'FreeText',
        'docFile' => 'settings-free-texts.md'],
    'einstellungen/vorlagen' => ['api' => 'TemplatesApi',
        'createOptions' => 'TemplateCreateOptions', 'updateOptions' => 'TemplateUpdateOptions',
        'docFile' => 'templates.md'],
    'einstellungen/mahnstufen' => ['api' => 'ReminderTextsApi', 'model' => 'ReminderText',
        'docFile' => 'settings-reminder-levels.md',
        'note' => 'Spec dokumentiert /reminder-texts; SDK ergänzt ein zweites DunningLevelsApi für /dunning-levels.'],
    'einstellungen/rollen' => ['api' => 'RolesApi', 'model' => 'Role',
        'docFile' => 'settings-roles.md',
        'note' => 'Read-only im SDK; Spec dokumentiert zusätzlich POST/PUT/DELETE auf /roles.'],
    'einstellungen/kunden-attribute' => ['api' => 'ClientPropertiesApi', 'docFile' => 'properties.md'],
    'einstellungen/artikel-attribute' => ['api' => 'ArticlePropertiesApi', 'docFile' => 'properties.md'],
    'einstellungen/lieferanten-attribute' => ['api' => 'SupplierPropertiesApi', 'docFile' => 'properties.md'],
    'einstellungen/eingangsrechnung-attribute' => ['api' => 'IncomingPropertiesApi', 'docFile' => 'properties.md'],
    'einstellungen/benutzer-attribute' => ['api' => 'UserPropertiesApi', 'model' => 'UserProperty',
        'docFile' => 'properties.md',
        'note' => 'Fünfter Parent neben article-/client-/supplier-/incoming-properties; nutzt geteilte PropertyCreateOptions.'],

    'grundlagen' => null,
    'grundlagen/authentifizierung' => null,
    'grundlagen/api-sicherheit' => null,
    'grundlagen/daten-lesen' => null,
    'grundlagen/daten-schreiben' => null,
    'grundlagen/eigene-meta-daten' => null,
    'grundlagen/fehler' => null,
    'grundlagen/tools' => null,
    'grundlagen/zugriffsbegrenzung' => null,
    'benutzerdefinierte-attribute-filtern' => null,
];

main($mapping);

/**
 * @param array<string, array<string, mixed>|null> $mapping
 */
function main(array $mapping): void
{
    if (!is_file(SPEC_PATH)) {
        throw new RuntimeException('Spec nicht gefunden: ' . SPEC_PATH . '. Bitte zuerst composer extract:spec ausführen.');
    }

    $spec = json_decode((string) file_get_contents(SPEC_PATH), true, 512, JSON_THROW_ON_ERROR);
    assert(is_array($spec) && isset($spec['resources']) && is_array($spec['resources']));

    $resources = $spec['resources'];
    ksort($resources, SORT_STRING);

    $matrixRows = buildResourceMatrix($resources, $mapping);
    $fieldGaps = buildFieldGaps($resources, $mapping);
    $enumGaps = buildEnumGaps($resources, $mapping);

    $markdown = renderMarkdown($matrixRows, $fieldGaps, $enumGaps);
    file_put_contents(AUDIT_PATH, $markdown);

    fwrite(STDOUT, sprintf("Audit nach %s geschrieben (%d Ressourcen).\n", AUDIT_PATH, count($matrixRows)));
}

/**
 * @param array<string, array<string, mixed>> $resources
 * @param array<string, array<string, mixed>|null> $mapping
 *
 * @return list<array<string, string>>
 */
function buildResourceMatrix(array $resources, array $mapping): array
{
    $rows = [];
    foreach ($resources as $slug => $resource) {
        if (!array_key_exists($slug, $mapping)) {
            $rows[] = [
                'slug' => $slug,
                'title' => (string) ($resource['title'] ?? $slug),
                'api' => '✗',
                'model' => '✗',
                'create' => '✗',
                'update' => '✗',
                'docFile' => '✗',
                'note' => 'Kein Mapping definiert – Audit-Skript erweitern.',
            ];
            continue;
        }

        $map = $mapping[$slug];
        if ($map === null) {
            // Bewusst übersprungen (Konzept-Doku unter grundlagen/)
            $rows[] = [
                'slug' => $slug,
                'title' => (string) ($resource['title'] ?? $slug),
                'api' => '—',
                'model' => '—',
                'create' => '—',
                'update' => '—',
                'docFile' => '—',
                'note' => 'Konzept-Doku, kein Api erwartet.',
            ];
            continue;
        }

        if (isset($map['conceptFile'])) {
            $conceptPath = (string) $map['conceptFile'];
            $rows[] = [
                'slug' => $slug,
                'title' => (string) ($resource['title'] ?? $slug),
                'api' => '—',
                'model' => '—',
                'create' => '—',
                'update' => '—',
                'docFile' => conceptMark($conceptPath),
                'note' => (string) ($map['note'] ?? 'Konzept-Doku, kein Api erwartet.'),
            ];
            continue;
        }

        $rows[] = [
            'slug' => $slug,
            'title' => (string) ($resource['title'] ?? $slug),
            'api' => classMark($map['api'] ?? null, API_NAMESPACE),
            'model' => classMark($map['model'] ?? null, 'Justpilot\\Billomat\\Model\\'),
            'create' => classMark($map['createOptions'] ?? null, API_NAMESPACE),
            'update' => classMark($map['updateOptions'] ?? null, API_NAMESPACE),
            'docFile' => docMark($map['docFile'] ?? null),
            'note' => (string) ($map['note'] ?? ''),
        ];
    }
    return $rows;
}

function classMark(?string $shortName, string $namespace): string
{
    if ($shortName === null) {
        return '—';
    }
    return class_exists($namespace . $shortName) ? '✓' : '✗';
}

function docMark(?string $relativePath): string
{
    if ($relativePath === null) {
        return '—';
    }
    return is_file(DOCS_RESOURCES_DIR . '/' . $relativePath) ? '✓' : '✗';
}

function conceptMark(string $relativePath): string
{
    return is_file(PROJECT_ROOT . '/docs/concepts/' . $relativePath) ? '✓' : '✗';
}

/**
 * @param array<string, array<string, mixed>> $resources
 * @param array<string, array<string, mixed>|null> $mapping
 *
 * @return list<array{slug: string, optionsClass: string, missingFields: list<string>}>
 */
function buildFieldGaps(array $resources, array $mapping): array
{
    $gaps = [];
    foreach ($resources as $slug => $resource) {
        $map = $mapping[$slug] ?? null;
        if (!is_array($map)) {
            continue;
        }

        foreach (['createOptions', 'updateOptions'] as $kind) {
            $shortName = $map[$kind] ?? null;
            if (!is_string($shortName)) {
                continue;
            }
            $fqcn = API_NAMESPACE . $shortName;
            if (!class_exists($fqcn)) {
                continue;
            }

            $expectedFieldNames = collectSpecFieldNames($resource, $kind);
            if ($expectedFieldNames === []) {
                continue;
            }

            $expectedFieldNames = filterParentIds($expectedFieldNames, $slug);

            $reflected = collectOptionPropertyNames($fqcn);
            $reflectedSet = array_flip(array_map(snakeCase(...), $reflected));

            $missing = [];
            foreach ($expectedFieldNames as $name) {
                if (!array_key_exists($name, $reflectedSet)) {
                    $missing[] = $name;
                }
            }
            if ($missing !== []) {
                sort($missing);
                $gaps[] = [
                    'slug' => $slug,
                    'optionsClass' => $shortName,
                    'missingFields' => $missing,
                ];
            }
        }
    }

    usort($gaps, static fn (array $a, array $b): int => $a['optionsClass'] <=> $b['optionsClass']);

    return $gaps;
}

/**
 * Liefert die Feldnamen, die für eine Create- oder Update-Klasse relevant sind.
 * Heuristik: nur der erste POST- bzw. PUT-Endpunkt zählt (das ist der kanonische
 * Create/Update; weitere POSTs/PUTs sind Aktionen wie „per E-Mail versenden",
 * „abschließen" etc. und haben eigene *EmailOptions/*MailOptions-Klassen).
 *
 * @param array<string, mixed> $resource
 *
 * @return list<string>
 */
function collectSpecFieldNames(array $resource, string $kind): array
{
    $method = $kind === 'createOptions' ? 'POST' : 'PUT';
    foreach ($resource['endpoints'] ?? [] as $endpoint) {
        if (($endpoint['method'] ?? null) !== $method) {
            continue;
        }
        $fields = [];
        foreach ($endpoint['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;
            if (is_string($name) && $name !== '') {
                $fields[$name] = true;
            }
        }
        $names = array_keys($fields);
        sort($names);
        return $names;
    }
    return [];
}

/**
 * @return list<string>
 */
function collectOptionPropertyNames(string $fqcn): array
{
    $names = [];
    $ref = new ReflectionClass($fqcn);
    foreach ($ref->getProperties() as $prop) {
        if ($prop->isPublic() && !$prop->isStatic()) {
            $names[] = $prop->getName();
        }
    }
    sort($names);
    return $names;
}

function snakeCase(string $camel): string
{
    $snake = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $camel) ?? $camel;
    return strtolower($snake);
}

/**
 * Entfernt Parent-ID-Felder aus der Erwartungsliste für Items, Kommentare, Schlagworte,
 * Zahlungen und Empfänger — diese werden vom parent-API-Code (z.B.
 * CreditNoteItemsApi::create($creditNoteId, …)) gesetzt und sind absichtlich nicht
 * als Property in der *Options-Klasse abgebildet.
 *
 * @param list<string> $fieldNames
 *
 * @return list<string>
 */
function filterParentIds(array $fieldNames, string $slug): array
{
    static $parentIdsBySegment = [
        'positionen' => ['invoice_id', 'offer_id', 'credit_note_id', 'confirmation_id', 'delivery_note_id', 'reminder_id', 'recurring_id'],
        'kommentare' => ['invoice_id', 'offer_id', 'credit_note_id', 'confirmation_id', 'delivery_note_id', 'reminder_id', 'letter_id', 'incoming_id'],
        'schlagworte' => ['invoice_id', 'offer_id', 'credit_note_id', 'confirmation_id', 'delivery_note_id', 'reminder_id', 'article_id', 'client_id', 'supplier_id', 'recurring_id', 'incoming_id', 'letter_id'],
        'zahlungen' => ['invoice_id', 'credit_note_id', 'incoming_id'],
        'empfaenger' => ['recurring_id'],
        'attribute' => ['client_id', 'article_id', 'supplier_id', 'incoming_id'],
    ];

    $parts = explode('/', $slug);
    $segment = end($parts) ?: $slug;
    $suppress = $parentIdsBySegment[$segment] ?? [];
    if ($suppress === []) {
        return $fieldNames;
    }

    return array_values(array_diff($fieldNames, $suppress));
}

/**
 * Vergleicht spec-Enums (Großbuchstaben-Listen) mit src/Model/Enum/*.php.
 *
 * @param array<string, array<string, mixed>> $resources
 * @param array<string, array<string, mixed>|null> $mapping
 *
 * @return list<array{enum: string, suggestion: string, missingValues: list<string>}>
 */
function buildEnumGaps(array $resources, array $mapping): array
{
    $sdkEnumCases = collectSdkEnumCases();
    $allKnown = [];
    foreach ($sdkEnumCases as $cases) {
        foreach ($cases as $c) {
            $allKnown[$c] = true;
        }
    }

    $reported = [];
    foreach ($resources as $slug => $resource) {
        foreach ($resource['endpoints'] ?? [] as $endpoint) {
            foreach ($endpoint['fields'] ?? [] as $field) {
                $enumValues = $field['enum'] ?? null;
                if (!is_array($enumValues) || $enumValues === []) {
                    continue;
                }
                $missing = array_values(array_diff($enumValues, array_keys($allKnown)));
                if ($missing === []) {
                    continue;
                }
                $suggestion = pascalCase((string) ($field['name'] ?? 'unbenannt'));
                $key = $suggestion . '|' . implode(',', $enumValues);
                if (isset($reported[$key])) {
                    continue;
                }
                $reported[$key] = true;
            }
        }
        unset($mapping); // unused, behalten für Lesbarkeit
    }

    // Liste aus den geflaggten Funden bauen.
    $gaps = [];
    foreach (array_keys($reported) as $key) {
        [$suggestion, $values] = explode('|', $key, 2);
        $valueList = explode(',', $values);
        $missing = array_values(array_diff($valueList, array_keys($allKnown)));
        $gaps[] = [
            'enum' => detectExistingEnum($valueList, $sdkEnumCases) ?? '(noch kein Enum)',
            'suggestion' => $suggestion,
            'missingValues' => $missing,
        ];
    }

    usort($gaps, static fn (array $a, array $b): int => $a['enum'] <=> $b['enum']);

    return $gaps;
}

/**
 * @param list<string> $values
 * @param array<string, list<string>> $sdkEnumCases
 */
function detectExistingEnum(array $values, array $sdkEnumCases): ?string
{
    foreach ($sdkEnumCases as $enumName => $cases) {
        $overlap = array_intersect($values, $cases);
        if (count($overlap) >= max(1, intdiv(count($values), 2))) {
            return $enumName;
        }
    }
    return null;
}

/**
 * @return array<string, list<string>>
 */
function collectSdkEnumCases(): array
{
    $dir = PROJECT_ROOT . '/src/Model/Enum';
    $out = [];
    foreach (glob($dir . '/*.php') ?: [] as $file) {
        $short = basename($file, '.php');
        $fqcn = ENUMS_NAMESPACE . $short;
        if (!enum_exists($fqcn)) {
            continue;
        }
        $ref = new ReflectionEnum($fqcn);
        $cases = [];
        foreach ($ref->getCases() as $case) {
            $cases[] = (string) ($case->getBackingValue() ?? $case->getName());
        }
        $out[$short] = $cases;
    }
    return $out;
}

function pascalCase(string $snake): string
{
    $parts = preg_split('/[_\s-]+/', $snake) ?: [];
    return implode('', array_map(static fn (string $p): string => ucfirst(strtolower($p)), $parts));
}

/**
 * @param list<array<string, string>> $matrixRows
 * @param list<array{slug: string, optionsClass: string, missingFields: list<string>}> $fieldGaps
 * @param list<array{enum: string, suggestion: string, missingValues: list<string>}> $enumGaps
 */
function renderMarkdown(array $matrixRows, array $fieldGaps, array $enumGaps): string
{
    $out = "# Billomat-API-Spec ↔ SDK-Code: Audit\n\n";
    $out .= "Generiert von `composer audit:spec` auf Basis von `docs/spec/billomat.json`\n";
    $out .= "und der aktuellen `src/Api/` + `src/Model/Enum/` + `docs/resources/`.\n\n";
    $out .= "Legende: ✓ vorhanden · ✗ fehlt · — nicht erwartet\n\n";

    $out .= "## Ressourcen-Matrix\n\n";
    $out .= "| Slug | Titel | Api | Model | CreateOpts | UpdateOpts | Doku | Notiz |\n";
    $out .= "|---|---|:-:|:-:|:-:|:-:|:-:|---|\n";
    foreach ($matrixRows as $row) {
        $out .= sprintf(
            "| `%s` | %s | %s | %s | %s | %s | %s | %s |\n",
            $row['slug'],
            escapeMd($row['title']),
            $row['api'],
            $row['model'],
            $row['create'],
            $row['update'],
            $row['docFile'],
            escapeMd($row['note']),
        );
    }

    $out .= "\n## Feld-Lücken in `*Options`-Klassen\n\n";
    if ($fieldGaps === []) {
        $out .= "_Keine Lücken auf Basis der aktuellen Spec gefunden._\n";
    } else {
        $out .= "Felder, die laut Spec dokumentiert sind, aber in der zugeordneten\n";
        $out .= "`*Options`-Klasse nicht als Property existieren (Vergleich auf snake_case).\n\n";
        $out .= "| Options-Klasse | Slug | Fehlende Felder |\n";
        $out .= "|---|---|---|\n";
        foreach ($fieldGaps as $gap) {
            $out .= sprintf(
                "| `%s` | `%s` | %s |\n",
                $gap['optionsClass'],
                $gap['slug'],
                implode(', ', array_map(static fn (string $f): string => '`' . $f . '`', $gap['missingFields'])),
            );
        }
    }

    $out .= "\n## Enum-Lücken\n\n";
    if ($enumGaps === []) {
        $out .= "_Keine Enum-Lücken erkannt._\n";
    } else {
        $out .= "Großbuchstaben-Wertelisten aus der Spec, deren Werte nicht durch ein\n";
        $out .= "bestehendes `Justpilot\\Billomat\\Model\\Enum\\*` abgedeckt sind.\n\n";
        $out .= "| Existierendes Enum | Vorschlag (PascalCase) | Fehlende Werte |\n";
        $out .= "|---|---|---|\n";
        foreach ($enumGaps as $gap) {
            $out .= sprintf(
                "| %s | `%s` | %s |\n",
                $gap['enum'],
                $gap['suggestion'],
                implode(', ', array_map(static fn (string $v): string => '`' . $v . '`', $gap['missingValues'])),
            );
        }
    }

    return $out;
}

function escapeMd(string $value): string
{
    return str_replace(['|', "\n"], ['\\|', ' '], $value);
}
