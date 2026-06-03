<!-- Quelle: https://www.billomat.com/api/einstellungen/email-vorlagen/ -->

# Email Templates (E-Mail-Vorlagen)

API-Wrapper für E-Mail-Vorlagen unter `/email-templates`. Vorlagen werden beim Versand von Dokumenten (Rechnungen, Angeboten, Mahnungen etc.) aus dem Billomat-UI ausgewählt; pro Dokumenttyp gibt es genau eine Standard-Vorlage.

## Zugriff

```php
$billomat->emailTemplates
```

`Justpilot\Billomat\Api\EmailTemplatesApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/email-templates` |
| `listPage($filters?)` | GET | `/email-templates` |
| `iterateAll($filters?, $pageSize?)` | GET | `/email-templates` (mehrseitig) |
| `get($id)` | GET | `/email-templates/{id}` |

> Read-only im SDK. Anlegen/Bearbeiten/Löschen sind bei Billomat verfügbar, aber nicht als SDK-Methoden exponiert.

## Methoden

### `list(array $filters = []): list<EmailTemplate>`

```php
foreach ($billomat->emailTemplates->list() as $tpl) {
    printf("#%d %s%s\n",
        $tpl->id,
        $tpl->name ?? '(ohne Name)',
        $tpl->isDefault ? ' (Default)' : '',
    );
}
```

Pagination via `page`/`per_page`; siehe [Konzept](../concepts/pagination-and-filtering.md).

### `listPage(array $filters = []): Page<EmailTemplate>`

Einzelne Seite samt `PageInfo`.

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<EmailTemplate>`

Lazy-Iteration.

### `get(int $id): ?EmailTemplate`

Liefert `null` bei 404.

## Read-Modell: `EmailTemplate`

`final readonly class EmailTemplate`.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `id` | `id` | `?int` |
| `name` | `name` | `?string` |
| `subject` | `subject` | `?string` |
| `body` | `body` | `?string` |
| `fromAddress` | `from` | `?string` |
| `isDefault` | `is_default` | `?bool` |

> Die Billomat-Spec listet zusätzlich `type` (Dokumenttyp, z.B. `INVOICES`/`OFFERS`/…) und `bcc` (BCC-Kopie an Absender). Das SDK exponiert diese Felder im Read-Modell aktuell nicht — Erweiterung ist trivial in `EmailTemplate::fromArray()`.

## Dokumenttypen (laut Spec)

| Wert | Verwendung |
|---|---|
| `INVOICES` | Rechnungs-Versand |
| `OFFERS` | Angebote |
| `CONFIRMATIONS` | Auftragsbestätigungen |
| `CREDIT_NOTES` | Gutschriften |
| `DELIVERY_NOTES` | Lieferscheine |
| `REMINDERS` | Mahnungen |
| `PAYMENT_THANKS` | Zahlungsbestätigung |

## Stolpersteine

- **`isDefault`-Eindeutigkeit pro Typ.** Pro Dokumenttyp ist genau eine Vorlage Default; das Setzen eines neuen Defaults überschreibt das alte serverseitig.
- **Single-Item-List-Quirk.** Bei nur einer Vorlage liefert Billomat ein Objekt statt einer Liste — `listResource()` normalisiert.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$defaults = array_filter(
    $billomat->emailTemplates->list(),
    static fn ($tpl) => $tpl->isDefault === true,
);

foreach ($defaults as $tpl) {
    printf("Default: #%d %s — Betreff '%s'\n",
        $tpl->id,
        $tpl->name ?? '',
        $tpl->subject ?? '',
    );
}
```
