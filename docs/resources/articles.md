<!-- Quelle: https://www.billomat.com/api/artikel/ -->

# Articles (Artikel)

API-Wrapper für Artikel unter `/articles` und ihre zwei Sub-Ressourcen (`/article-tags`, `/article-property-values`).

## Zugriff

```php
$billomat->articles               // Artikel selbst
$billomat->articleTags            // Schlagworte an einem Artikel
$billomat->articlePropertyValues  // Werte benutzerdefinierter Artikel-Eigenschaften
```

Die Definitionen der Eigenschaften (Property-Schemata) liegen separat unter `$billomat->articleProperties` — siehe [Property-Definitionen](properties.md). Hier geht es ausschließlich um die Werte, die ein konkreter Artikel für diese Definitionen trägt.

## Modell

Ein Artikel ist ein wiederverwendbarer Posten, der in Rechnungen, Angeboten und Abo-Rechnungen als Position referenziert werden kann.

- **Identifikation**: laufende `number` mit `numberPre` + `numberLength` ergibt die anzeigbare `article_number`.
- **Preise**: bis zu fünf Verkaufspreis-Stufen (`salesPrice` bis `salesPrice5`) für Staffelpreise/Kundengruppen, optional ein `purchasePrice` mit eigener Währung.
- **Zuordnung**: `taxId` verweist auf einen [Steuersatz](taxes.md), `unitId` auf eine [Unit](lookups.md), und `supplierId` auf einen [Supplier](suppliers.md). `categoryId` referenziert die Account-internen Artikel-Kategorien.
- **Custom-Fields**: dynamische Werte über `articlePropertyValues` (z. B. „Lieferzeit“, „Garantie“) — die Property-Definitionen selbst werden über `articleProperties` gepflegt.

## Endpunkt-Übersicht

### `/articles`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/articles` |
| `get($id)` | GET | `/articles/{id}` |
| `create($options)` | POST | `/articles` |
| `update($id, $options)` | PUT | `/articles/{id}` |
| `delete($id)` | DELETE | `/articles/{id}` |

### `/article-tags`

| Methode | HTTP | Pfad |
|---|---|---|
| `listByArticle($articleId)` | GET | `/article-tags?article_id={id}` |
| `cloud()` | GET | `/article-tags` |
| `get($id)` | GET | `/article-tags/{id}` |
| `create($options)` | POST | `/article-tags` |
| `delete($id)` | DELETE | `/article-tags/{id}` |

### `/article-property-values`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/article-property-values` |
| `get($id)` | GET | `/article-property-values/{id}` |
| `create($options)` | POST | `/article-property-values` |

## Articles

### `list(array $filters = []): list<Article>`

Filter laut Billomat-Doku: `article_number`, `title`, `description`, `currency_code`, `unit_id`, `tags`, `supplier_id`, `category_id`. Array-Werte werden als `key[]=…` codiert.

```php
$active = $billomat->articles->list([
    'currency_code' => 'EUR',
    'order_by' => 'title+ASC',
]);
```

### `get(int $id): ?Article`

Liefert `null` bei 404.

### `create(ArticleCreateOptions $options): Article`

```php
use Justpilot\Billomat\Api\ArticleCreateOptions;

$opts = new ArticleCreateOptions(title: 'Webhosting Basic');
$opts->description = 'Shared Hosting, 10 GB SSD';
$opts->salesPrice = 19.90;
$opts->currencyCode = 'EUR';
$opts->unit = 'Monat';
$opts->taxId = 12345;

$article = $billomat->articles->create($opts);
```

### `update(int $id, ArticleUpdateOptions $options): Article`

Partial-Update — nicht gesetzte Properties bleiben unangetastet.

### `delete(int $id): bool`

## Write-Modell: `ArticleCreateOptions`

Konstruktor: `new ArticleCreateOptions(string $title)` — `title` ist Pflicht.

### Identifikation

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `title` | `title` | `string` (Pflicht) | — |
| `numberPre` | `number_pre` | `?string` | Präfix der Artikelnummer (z. B. `ART-`) |
| `number` | `number` | `?int` | Laufende Nummer; Billomat zählt sonst selbst hoch |
| `numberLength` | `number_length` | `?int` | Min-Stellenzahl der Nummer mit Nullen-Padding |
| `description` | `description` | `?string` | — |

### Preise

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `salesPrice` | `sales_price` | `?float` | Standard-Verkaufspreis |
| `salesPrice2` | `sales_price2` | `?float` | Stufe 2 (z. B. Wiederverkäufer) |
| `salesPrice3` | `sales_price3` | `?float` | Stufe 3 |
| `salesPrice4` | `sales_price4` | `?float` | Stufe 4 |
| `salesPrice5` | `sales_price5` | `?float` | Stufe 5 |
| `currencyCode` | `currency_code` | `?string` | ISO-4217, z. B. `EUR` |
| `purchasePrice` | `purchase_price` | `?float` | Einkaufspreis |
| `purchasePriceCurrencyCode` | `purchase_price_currency_code` | `?string` | Eigene Währung für den EK |

### Zuordnungen

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `unit` | `unit` | `?string` | Freitext-Einheit (z. B. `Stück`) — alternativ zu `unitId` |
| `unitId` | `unit_id` | `?int` | Referenz auf `/units` |
| `supplierId` | `supplier_id` | `?int` | Referenz auf [Supplier](suppliers.md) |
| `taxId` | `tax_id` | `?int` | Referenz auf [Taxes](taxes.md) |
| `categoryId` | `category_id` | `?int` | Referenz auf `/article-categories` |

## Write-Modell: `ArticleUpdateOptions`

Spiegelt `ArticleCreateOptions` exakt, jedoch ist `title` nicht mehr Pflicht und alle Felder sind nullable. Nicht gesetzte Properties bleiben unverändert.

## Read-Modell: `Article`

`final readonly class Article`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `articleNumber` | `?string` (zusammengesetzt: `numberPre` + `number` mit Padding) |
| `number` | `?int` |
| `numberPre` | `?string` |
| `numberLength` | `?int` |
| `title` | `?string` |
| `description` | `?string` |
| `salesPrice` | `?float` |
| `salesPrice2` ... `salesPrice5` | `?float` |
| `currencyCode` | `?string` |
| `unit` | `?string` |
| `unitId` | `?int` |
| `purchasePrice` | `?float` |
| `purchasePriceCurrencyCode` | `?string` |
| `supplierId` | `?int` |
| `taxId` | `?int` |
| `categoryId` | `?int` |

## Article Tags

Funktional identisch zu [Invoice Tags](invoice-tags.md), bezogen auf einen Artikel statt eine Rechnung.

### Methoden

```php
use Justpilot\Billomat\Api\ArticleTagCreateOptions;

$tag = $billomat->articleTags->create(
    new ArticleTagCreateOptions(articleId: $article->id, name: 'hosting'),
);

$tags  = $billomat->articleTags->listByArticle($article->id);
$cloud = $billomat->articleTags->cloud(); // aggregiert, mit count
$billomat->articleTags->delete($tag->id);
```

Ein `update()` gibt es nicht — Tags werden gelöscht und neu angelegt.

### Write-Modell: `ArticleTagCreateOptions`

Beide Werte sind Pflicht und werden über den Konstruktor gesetzt:

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `articleId` | `article_id` | `int` (Pflicht) | — |
| `name` | `name` | `string` (Pflicht) | — |

### Read-Modell: `ArticleTag`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `articleId` | `int` |
| `name` | `string` |

### Read-Modell: `ArticleTagCloudEntry`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Article Property Values

Speichert die Werte für die in [`properties.md`](properties.md) definierten Artikel-Eigenschaften (`articleProperties`). Eine Property-Definition (`ArticleProperty`) ist das Schema (Name + `PropertyType`), ein `ArticlePropertyValue` ist die konkrete Belegung an einem Artikel.

```php
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;

// Werte für einen Artikel auflisten
$values = $billomat->articlePropertyValues->list([
    'article_id' => $article->id,
]);

// Wert für die Property "Lieferzeit" (id = 42) setzen
$value = $billomat->articlePropertyValues->create(
    new ArticlePropertyValueCreateOptions(
        articleId: $article->id,
        articlePropertyId: 42,
        value: '3-5 Werktage',
    ),
);
```

Es gibt weder `update()` noch `delete()` — ein erneuter `create()`-Call mit derselben `articlePropertyId` überschreibt den bestehenden Wert.

### Write-Modell: `ArticlePropertyValueCreateOptions`

Alle drei Werte sind Pflicht und werden über den Konstruktor gesetzt:

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `articleId` | `article_id` | `int` (Pflicht) | — |
| `articlePropertyId` | `article_property_id` | `int` (Pflicht) | ID der Property-Definition |
| `value` | `value` | `mixed` (Pflicht) | Bei `CHECKBOX` werden `0`/`1` erwartet |

### Read-Modell: `ArticlePropertyValue`

| Property | Typ |
|---|---|
| `id` | `?int` |
| `articleId` | `int` |
| `articlePropertyId` | `int` |
| `type` | `?string` (rohes API-Feld, z. B. `TEXTFIELD`) |
| `name` | `?string` (Name aus der Property-Definition) |
| `value` | `mixed` |

## Verwendete Enums

- [`PropertyType`](../../src/Model/Enum/PropertyType.php): `TEXTFIELD`, `TEXTAREA`, `CHECKBOX` — wird vom zugehörigen [`ArticleProperty`](../../src/Model/ArticleProperty.php) der Definition getragen, nicht vom Wert selbst.

## Stolpersteine

- **Kein `update()` für Tags und Property-Values.** Tags löschen und neu anlegen; bei Property-Values überschreibt ein erneuter POST mit derselben `article_property_id` den bestehenden Eintrag.
- **`unit` vs. `unitId`.** Beide Felder existieren parallel: `unit` ist ein freier String, `unitId` ein Verweis auf `/units`. Bei beidem zeitgleich kann das Anzeigeverhalten in Belegen abweichen — nur eines setzen.
- **`number` wird sonst automatisch vergeben.** Ohne explizites `number` zählt Billomat anhand der zuletzt vergebenen Artikelnummer hoch. Eine manuelle `number` muss eindeutig sein, sonst gibt es `422`.
- **`articleNumber` ist read-only.** Das Feld erscheint nur im Read-Modell und wird serverseitig aus `numberPre` + `number` (gepaddet auf `numberLength`) gebildet.
- **`purchasePriceCurrencyCode` kann von `currencyCode` abweichen.** Für einen Artikel mit EK in USD und VK in EUR beide Felder setzen.
- **`PropertyType::CHECKBOX` erwartet `0`/`1` als `value`.** Boolean-Strings (`"true"`/`"false"`) werden nicht erkannt.
- **`ArticlePropertyValue::$type` ist String, nicht Enum.** Beim Wert selbst kommt der `type` als roher API-String mit; das `PropertyType`-Enum lebt nur im `ArticleProperty`-Read-Modell (Definition).

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;
use Justpilot\Billomat\Api\ArticleTagCreateOptions;
use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Artikel anlegen
$opts = new ArticleCreateOptions(title: 'Webhosting Basic');
$opts->description = 'Shared Hosting, 10 GB SSD, tägliches Backup';
$opts->salesPrice = 19.90;
$opts->salesPrice2 = 17.90; // Stufe für Wiederverkäufer
$opts->currencyCode = 'EUR';
$opts->unit = 'Monat';
$opts->taxId = 12345;

$article = $billomat->articles->create($opts);
printf("Artikel #%d (%s) angelegt\n", $article->id, $article->articleNumber);

// 2) Tags vergeben — für spätere Filterung
$billomat->articleTags->create(
    new ArticleTagCreateOptions(articleId: $article->id, name: 'hosting'),
);
$billomat->articleTags->create(
    new ArticleTagCreateOptions(articleId: $article->id, name: 'subscription'),
);

// 3) Custom-Property pflegen ("Lieferzeit" = ArticleProperty #42)
$billomat->articlePropertyValues->create(
    new ArticlePropertyValueCreateOptions(
        articleId: $article->id,
        articlePropertyId: 42,
        value: 'Sofort verfügbar',
    ),
);

// 4) Preis später anpassen — Partial-Update
$update = new ArticleUpdateOptions();
$update->salesPrice = 24.90;
$billomat->articles->update($article->id, $update);

// 5) Cloud-Übersicht aller Artikel-Tags
foreach ($billomat->articleTags->cloud() as $entry) {
    printf("%s (%d)\n", $entry->name, $entry->count);
}
```
