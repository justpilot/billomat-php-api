<!-- Quelle: https://www.billomat.com/api/einstellungen/vorlagen/ -->

# Templates (Vorlagen)

API-Wrapper für Dokument-Vorlagen unter `/templates`. Vorlagen werden für die PDF-Erzeugung von Rechnungen, Angeboten, Lieferscheinen usw. genutzt — sowohl Billomat-interne („DEFINED“, im Editor gebaut) als auch eigene Word/RTF-Uploads („UPLOADED“).

## Zugriff

```php
$billomat->templates
```

`Justpilot\Billomat\Api\TemplatesApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($query?)` | GET | `/templates` |
| `get($id)` | GET | `/templates/{id}` |
| `create($options)` | POST | `/templates` |
| `update($id, $options)` | PUT | `/templates/{id}` |
| `delete($id)` | DELETE | `/templates/{id}` |
| `thumb($id, $format?)` | GET | `/templates/{id}/thumb` |

## Methoden

### `list(array $query = []): list<Template>`

```php
$templates = $billomat->templates->list();

foreach ($templates as $tpl) {
    printf("[#%d] %s (%s)\n",
        $tpl->id,
        $tpl->name ?? '(ohne Name)',
        $tpl->type?->label(),
    );
}
```

### `get(int $id): ?Template`

Liefert `null` bei 404. Bei `UPLOADED`-Templates enthält der Single-GET zusätzlich `format` und `base64file` — bei `DEFINED` nicht.

### `create(TemplateCreateOptions $options): Template`

```php
use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;

// DEFINED (Editor-Vorlage)
$defined = new TemplateCreateOptions(TemplateDocumentType::INVOICE);
$defined->name = 'Standard-Rechnung';
$defined->isDefault = true;

$billomat->templates->create($defined);

// UPLOADED (eigene DOCX-Datei)
$uploaded = new TemplateCreateOptions(TemplateDocumentType::OFFER);
$uploaded->name = 'Angebot 2026';
$uploaded->format = TemplateFormat::DOCX;
$uploaded->base64file = base64_encode(file_get_contents('/path/to/template.docx'));

$billomat->templates->create($uploaded);
```

Wird `format` + `base64file` gesetzt, ist Billomat das Signal, die Vorlage als `UPLOADED` zu speichern. Ohne diese Felder entsteht eine `DEFINED`-Vorlage.

### `update(int $id, TemplateUpdateOptions $options): Template`

Aktualisiert ausschließlich `name` und `isDefault`. Vorlagen-Inhalte (DOCX/RTF) lassen sich nicht via PUT überschreiben — dafür müsste die Vorlage gelöscht und neu hochgeladen werden.

### `delete(int $id): bool`

### `thumb(int $id, TemplateThumbFormat $format = TemplateThumbFormat::PNG): string`

Lädt ein Vorschaubild als Binärdaten. Format wählbar (PNG, GIF, JPG).

```php
use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;

$png = $billomat->templates->thumb(42, TemplateThumbFormat::PNG);
file_put_contents('preview.png', $png);
```

## Write-Modell: `TemplateCreateOptions`

Konstruktor: `new TemplateCreateOptions(TemplateDocumentType $type)`.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `type` | `type` | `TemplateDocumentType` | Pflicht (Konstruktor). Dokumenttyp: Rechnung, Angebot, Mahnung … |
| `name` | `name` | `?string` | |
| `format` | `format` | `?TemplateFormat` | `doc`, `docx` oder `rtf`. Nur in Kombination mit `base64file` sinnvoll. |
| `base64file` | `base64file` | `?string` | Base64-kodierte Vorlagen-Datei (nur für UPLOADED). |
| `isDefault` | `is_default` | `?bool` | Wird als `1`/`0` serialisiert. |

`toArray()` filtert `null` heraus.

## Write-Modell: `TemplateUpdateOptions`

| Property | Billomat-Feld | Typ |
|---|---|---|
| `name` | `name` | `?string` |
| `isDefault` | `is_default` | `?bool` |

## Read-Modell: `Template`

`final readonly class Template`.

| Property | Typ | Notes |
|---|---|---|
| `id` | `?int` | |
| `created` | `?\DateTimeImmutable` | |
| `type` | `?TemplateDocumentType` | INVOICE, OFFER, … |
| `templateType` | `?TemplateType` | DEFINED oder UPLOADED |
| `name` | `?string` | |
| `format` | `?TemplateFormat` | Nur bei UPLOADED + Single-GET |
| `base64file` | `?string` | Nur bei UPLOADED + Single-GET |
| `isDefault` | `bool` | |

## Verwendete Enums

- [`TemplateDocumentType`](../../src/Model/Enum/TemplateDocumentType.php): `INVOICE`, `OFFER`, `CONFIRMATION`, `REMINDER`, `DELIVERY_NOTE`, `CREDIT_NOTE`, `LETTER`. Hat `label(): string` für UI-Anzeige (z. B. „Rechnung“, „Angebot“).
- [`TemplateType`](../../src/Model/Enum/TemplateType.php): `DEFINED`, `UPLOADED`.
- [`TemplateFormat`](../../src/Model/Enum/TemplateFormat.php): `doc`, `docx`, `rtf`.
- [`TemplateEngine`](../../src/Model/Enum/TemplateEngine.php): `DEFAULT` (Billomat hat aktuell nur eine Engine, das Enum bildet das ab).
- [`TemplateThumbFormat`](../../src/Model/Enum/TemplateThumbFormat.php): `png`, `gif`, `jpg`.

## Stolpersteine

- **`format` + `base64file` nur gemeinsam.** Wer eine `UPLOADED`-Vorlage anlegt, muss beide Felder setzen. Sonst landet Billomat bei einer leeren DEFINED-Vorlage.
- **Default-Flag ist exklusiv pro `type`.** Setzt du `isDefault = true` auf einer INVOICE-Vorlage, verliert die vorherige Default-INVOICE-Vorlage das Flag — andere Dokumenttypen sind unberührt.
- **`format`/`base64file` fehlen in der Liste.** Bei `list()` liefert Billomat nur die Metadaten. Vollständig sind die Felder erst nach einem Single-GET (`get($id)`) — und auch nur bei UPLOADED-Templates.
- **`thumb()` umgeht `getJson()`.** Die Methode benutzt `getRaw()` aus `AbstractApi` — Rückgabe ist immer Binär-String, keine Hülle.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Api\TemplateUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// Liste durchgehen
foreach ($billomat->templates->list() as $tpl) {
    printf("[#%d] %s — %s\n",
        $tpl->id,
        $tpl->name ?? '(ohne Name)',
        $tpl->type?->label() ?? '?',
    );
}

// Eigene DOCX-Vorlage hochladen
$opts = new TemplateCreateOptions(TemplateDocumentType::INVOICE);
$opts->name = 'Mein Briefkopf 2026';
$opts->format = TemplateFormat::DOCX;
$opts->base64file = base64_encode(file_get_contents('/path/to/invoice-template.docx'));

$created = $billomat->templates->create($opts);

// Als Default markieren
$update = new TemplateUpdateOptions();
$update->isDefault = true;
$billomat->templates->update($created->id, $update);

// Vorschaubild speichern
$thumb = $billomat->templates->thumb($created->id, TemplateThumbFormat::PNG);
file_put_contents(sprintf('template-%d.png', $created->id), $thumb);
```
