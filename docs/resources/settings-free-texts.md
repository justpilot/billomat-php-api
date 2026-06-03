<!-- Quelle: https://www.billomat.com/api/einstellungen/freitexte/ -->

# Free Texts (Freitexte)

API-Wrapper für Freitext-Bausteine unter `/free-texts`. Freitexte sind vorgefertigte Texte für die Felder `title`, `label`, `intro`, `note` auf Dokumenten (Rechnungen, Angeboten, Lieferscheinen, Briefen etc.). Sie unterstützen Billomat-Platzhalter wie `[Invoice.invoice_number]` oder `[Invoice.due_date]`.

## Zugriff

```php
$billomat->freeTexts
```

`Justpilot\Billomat\Api\FreeTextsApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/free-texts` |
| `listPage($filters?)` | GET | `/free-texts` |
| `iterateAll($filters?, $pageSize?)` | GET | `/free-texts` (mehrseitig) |
| `get($id)` | GET | `/free-texts/{id}` |

> Read-only im SDK. Anlegen/Bearbeiten/Löschen sind bei Billomat verfügbar, aber nicht als SDK-Methoden exponiert.

## Methoden

### `list(array $filters = []): list<FreeText>`

```php
foreach ($billomat->freeTexts->list() as $ft) {
    printf("#%d %s — %s\n",
        $ft->id,
        $ft->title ?? '(ohne Titel)',
        $ft->label ?? '',
    );
}
```

Pagination via `page`/`per_page`; siehe [Konzept](../concepts/pagination-and-filtering.md).

### `listPage(array $filters = []): Page<FreeText>`

Einzelne Seite samt `PageInfo`.

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<FreeText>`

Lazy-Iteration.

### `get(int $id): ?FreeText`

Liefert `null` bei 404.

## Read-Modell: `FreeText`

`final readonly class FreeText`.

| Property | Billomat-Feld | Typ | Zweck |
|---|---|---|---|
| `id` | `id` | `?int` | |
| `title` | `title` | `?string` | Überschrift bzw. Betreff des Dokuments |
| `label` | `label` | `?string` | Untertitel/Bezeichnung |
| `intro` | `intro` | `?string` | Einleitungstext / Brieftext |
| `note` | `note` | `?string` | Anmerkungen am Dokumentende |

> Die Billomat-Spec listet zusätzlich `name` (interner Bezeichner der Vorlage), `type` (Dokumenttyp) und `is_default`. Das SDK exponiert diese Felder im Read-Modell aktuell nicht — die Erweiterung in `FreeText::fromArray()` ist trivial.

## Dokumenttypen (laut Spec)

| Wert | Verwendung |
|---|---|
| `INVOICE` | Rechnung |
| `CORRECTION` | Korrektur |
| `OFFER` | Angebot |
| `CONFIRMATION` | Auftragsbestätigung |
| `CREDIT_NOTE` | Gutschrift |
| `DELIVERY_NOTE` | Lieferschein |
| `LETTER` | Brief |

## Platzhalter

In allen Textfeldern lassen sich Billomat-Platzhalter verwenden, z.B.:

- `[Invoice.invoice_number]` — Rechnungsnummer
- `[Invoice.date]` — Rechnungsdatum
- `[Invoice.due_date]` — Fälligkeitsdatum
- `[Client.name]` — Kundenname

Die vollständige Platzhalter-Liste hängt am Dokumenttyp und wird im Billomat-UI unter *Einstellungen → Vorlagen* dokumentiert.

## Stolpersteine

- **`is_default` pro Typ exklusiv.** Pro Dokumenttyp ist genau ein Freitext Default; das Setzen eines neuen Defaults überschreibt das alte serverseitig.
- **Single-Item-List-Quirk.** Bei nur einem Freitext im Account liefert Billomat ein Objekt statt einer Liste — `listResource()` normalisiert.

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

foreach ($billomat->freeTexts->iterateAll() as $ft) {
    if ($ft->intro !== null && str_contains($ft->intro, '[Invoice')) {
        printf("Vorlage #%d enthält Rechnungs-Platzhalter\n", $ft->id);
    }
}
```
