<!-- Source: https://www.billomat.com/api/einstellungen/vorlagen/ -->

# Templates (Vorlagen)

API wrapper for document templates at `/templates`. Templates are used to render PDFs of invoices, offers, delivery notes, etc. — both Billomat's own ("DEFINED", built in the editor) and your own Word/RTF uploads ("UPLOADED").

## Access

```php
$billomat->templates
```

`Justpilot\Billomat\Api\TemplatesApi`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($query?)` | GET | `/templates` |
| `get($id)` | GET | `/templates/{id}` |
| `getOrFail($id)` | GET | `/templates/{id}` |
| `create($options)` | POST | `/templates` |
| `update($id, $options)` | PUT | `/templates/{id}` |
| `delete($id)` | DELETE | `/templates/{id}` |
| `thumb($id, $format?)` | GET | `/templates/{id}/thumb` |

## Methods

### `list(TemplateFilter|array $query = []): list<Template>`

Can be filtered with the typed `TemplateFilter`: `type` (document type, `list<TemplateDocumentType>`, sent comma-separated → Billomat filter `type`), `templateType` (`TemplateType::DEFINED`/`UPLOADED` → `template_type`) and `isDefault` (→ `is_default`), plus `orderBy`, `page`, `perPage` and `extra` for further query parameters. A plain array still works.

```php
use Justpilot\Billomat\Api\TemplateFilter;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;

$templates = $billomat->templates->list();

// Invoice templates only
$invoiceTemplates = $billomat->templates->list(new TemplateFilter(
    type: [TemplateDocumentType::INVOICE],
));

foreach ($templates as $tpl) {
    printf("[#%d] %s (%s)\n",
        $tpl->id,
        $tpl->name ?? '(no name)',
        $tpl->type?->label(),
    );
}
```

### `get(int $id): ?Template`

Returns `null` on 404. For `UPLOADED` templates the single GET also contains `format` and `base64file`; for `DEFINED` it doesn't.

### `getOrFail(int $id): Template`

Like `get()`, but throws `NotFoundException` instead of returning `null`.

### `create(TemplateCreateOptions $options): Template`

```php
use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;

// DEFINED (editor template)
$defined = new TemplateCreateOptions(
    TemplateDocumentType::INVOICE,
    name: 'Standard-Rechnung',
    isDefault: true,
);

$billomat->templates->create($defined);

// UPLOADED (your own DOCX file)
$uploaded = new TemplateCreateOptions(
    TemplateDocumentType::OFFER,
    name: 'Angebot 2026',
    format: TemplateFormat::DOCX,
    base64file: base64_encode(file_get_contents('/path/to/template.docx')),
);

$billomat->templates->create($uploaded);
```

Setting `format` + `base64file` tells Billomat to store the template as `UPLOADED`. Without these fields you get a `DEFINED` template.

### `update(int $id, TemplateUpdateOptions $options): Template`

Updates only `name` and `isDefault`. Template contents (DOCX/RTF) can't be replaced via PUT; delete the template and upload it again.

### `delete(int $id): void`

Deletes the template.

### `thumb(int $id, TemplateThumbFormat $format = TemplateThumbFormat::PNG): string`

Downloads a preview image as binary data. Format: PNG, GIF or JPG.

```php
use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;

$png = $billomat->templates->thumb(42, TemplateThumbFormat::PNG);
file_put_contents('preview.png', $png);
```

## Write model: `TemplateCreateOptions`

Constructor: `new TemplateCreateOptions(TemplateDocumentType $type, …)` — `type` is required and the first parameter; all other fields are named arguments. The object is `final readonly`; the `with*()` methods (e.g. `$opts->withName('New')`) return a modified copy.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `type` | `type` | `TemplateDocumentType` | Required (constructor). Document type: invoice, offer, reminder … |
| `name` | `name` | `?string` | |
| `format` | `format` | `?TemplateFormat` | `doc`, `docx` or `rtf`. Only useful together with `base64file`. |
| `base64file` | `base64file` | `?string` | Base64-encoded template file (UPLOADED only). |
| `isDefault` | `is_default` | `?bool` | Serialized as `1`/`0`. |

`toArray()` strips `null` values.

## Write model: `TemplateUpdateOptions`

`final readonly` like all write models; pass fields as named arguments or use `with*()`. `null` means "don't send", the value stays unchanged. With `Clear::Field` (`Justpilot\Billomat\Api\Clear`) the field is sent as JSON `null` and Billomat clears it.

| Property | Billomat field | Type |
|---|---|---|
| `name` | `name` | `string\|Clear\|null` |
| `isDefault` | `is_default` | `bool\|Clear\|null` |

## Read model: `Template`

`final readonly class Template`.

| Property | Type | Notes |
|---|---|---|
| `id` | `?int` | |
| `created` | `?\DateTimeImmutable` | |
| `type` | `?TemplateDocumentType` | INVOICE, OFFER, … |
| `templateType` | `?TemplateType` | DEFINED or UPLOADED |
| `name` | `?string` | |
| `format` | `?TemplateFormat` | Only for UPLOADED + single GET |
| `base64file` | `?string` | Only for UPLOADED + single GET |
| `isDefault` | `bool` | |
| `updated` | `?\DateTimeImmutable` |  |
| `customfield` | `?string` | Free extra field, see [Custom meta attributes](../concepts/custom-meta-attributes.md) |
| `isBackgroundAvailable` | `?bool` |  |
| `extra` | `array<string,mixed>` | Fields the SDK doesn't map yet, raw (see [Read models](../concepts/models.md)) |

## Enums

- [`TemplateDocumentType`](../../src/Model/Enum/TemplateDocumentType.php): `INVOICE`, `OFFER`, `CONFIRMATION`, `REMINDER`, `DELIVERY_NOTE`, `CREDIT_NOTE`, `LETTER`. Has `label(): string` with the German label for display (e.g. "Rechnung", "Angebot").
- [`TemplateType`](../../src/Model/Enum/TemplateType.php): `DEFINED`, `UPLOADED`.
- [`TemplateFormat`](../../src/Model/Enum/TemplateFormat.php): `DOC`, `DOCX`, `RTF` (values `doc`, `docx`, `rtf`).
- [`TemplateEngine`](../../src/Model/Enum/TemplateEngine.php): `DEFAULT` (Billomat currently has only one engine; used in [Settings](settings.md)).
- [`TemplateThumbFormat`](../../src/Model/Enum/TemplateThumbFormat.php): `PNG`, `GIF`, `JPG` (values `png`, `gif`, `jpg`).

## Pitfalls

- **`format` + `base64file` only together.** To create an `UPLOADED` template, set both fields. Otherwise Billomat creates an empty DEFINED template.
- **The default flag is exclusive per `type`.** If you set `isDefault = true` on an INVOICE template, the previous default INVOICE template loses the flag; other document types are not affected.
- **`format`/`base64file` are missing in the list.** `list()` only returns metadata. The fields are only filled after a single GET (`get($id)`), and only for UPLOADED templates.
- **`thumb()` bypasses `getJson()`.** The method uses `getRaw()` from `AbstractApi`; it always returns a binary string, no envelope.

## End-to-end example

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

// Walk through the list
foreach ($billomat->templates->list() as $tpl) {
    printf("[#%d] %s — %s\n",
        $tpl->id,
        $tpl->name ?? '(no name)',
        $tpl->type?->label() ?? '?',
    );
}

// Upload your own DOCX template
$opts = new TemplateCreateOptions(
    TemplateDocumentType::INVOICE,
    name: 'Mein Briefkopf 2026',
    format: TemplateFormat::DOCX,
    base64file: base64_encode(file_get_contents('/path/to/invoice-template.docx')),
);

$created = $billomat->templates->create($opts);

// Make it the default
$billomat->templates->update($created->id, new TemplateUpdateOptions(isDefault: true));

// Save the preview image
$thumb = $billomat->templates->thumb($created->id, TemplateThumbFormat::PNG);
file_put_contents(sprintf('template-%d.png', $created->id), $thumb);
```
