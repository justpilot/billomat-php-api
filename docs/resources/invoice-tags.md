<!-- Quelle: https://www.billomat.com/api/rechnungen/schlagworte/ -->

# Invoice Tags (Schlagworte)

API-Wrapper für Schlagworte an Rechnungen unter `/invoice-tags`.

## Zugriff

```php
$billomat->invoiceTags
```

`Justpilot\Billomat\Api\InvoiceTagsApi`.

## Überblick

Tags an Rechnungen funktionieren wie frei wählbare Stichworte (`wichtig`, `A-Kunde`, `Saison`). Dieselbe Ressource liefert zwei verschiedene Sichten:

- **Tags einer Rechnung** — über `?invoice_id={id}`. Response-Element: `invoice-tag`.
- **Tag-Cloud** — alle Tags des Accounts mit Häufigkeit, ohne Filter. Response-Element: `tag` (anderer Key!).

Das SDK trennt das in zwei Methoden mit zwei separaten Read-Modellen.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `listByInvoice($invoiceId)` | GET | `/invoice-tags?invoice_id={id}` |
| `cloud()` | GET | `/invoice-tags` |
| `get($id)` | GET | `/invoice-tags/{id}` |
| `create($options)` | POST | `/invoice-tags` |
| `delete($id)` | DELETE | `/invoice-tags/{id}` |

Es gibt keine `update()`-Methode — ein Tag wird gelöscht und neu angelegt.

## Methoden

### `listByInvoice(int $invoiceId): list<InvoiceTag>`

Listet die Schlagworte einer einzelnen Rechnung.

```php
$tags = $billomat->invoiceTags->listByInvoice(98765);

foreach ($tags as $tag) {
    echo $tag->name . "\n";
}
```

### `cloud(): list<InvoiceTagCloudEntry>`

Aggregierte Liste aller Tags im Account mit `count`-Häufigkeit. Praktisch für UI-Autovervollständigung oder eine Tag-Cloud-Anzeige.

```php
foreach ($billomat->invoiceTags->cloud() as $entry) {
    printf("%s (%d Rechnungen)\n", $entry->name, $entry->count);
}
```

### `get(int $id): ?InvoiceTag`

Liefert `null` bei 404.

### `create(InvoiceTagCreateOptions $options): InvoiceTag`

```php
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;

$tag = $billomat->invoiceTags->create(
    new InvoiceTagCreateOptions(invoiceId: 98765, name: 'A-Kunde'),
);
```

Mehrere Tags für dieselbe Rechnung → mehrere `create()`-Aufrufe.

### `delete(int $id): bool`

Entfernt die Verknüpfung zwischen Rechnung und Tag. Wird das Schlagwort an keiner Rechnung mehr verwendet, verschwindet es automatisch aus der Tag-Cloud.

## Write-Modell: `InvoiceTagCreateOptions`

Konstruktor: `new InvoiceTagCreateOptions(int $invoiceId, string $name)`. Beide Werte sind Pflicht.

| Property | Billomat-Feld | Typ |
|---|---|---|
| `invoiceId` | `invoice_id` | `int` |
| `name` | `name` | `string` |

## Read-Modell: `InvoiceTag`

`final readonly class InvoiceTag` — eine konkrete Tag-Verknüpfung an einer Rechnung.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `invoiceId` | `int` |
| `name` | `string` |

## Read-Modell: `InvoiceTagCloudEntry`

`final readonly class InvoiceTagCloudEntry` — ein aggregierter Eintrag aus `cloud()`. Hat bewusst **kein** `invoiceId`-Feld, weil der Eintrag mehrere Rechnungen aggregiert.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Stolpersteine

- **Zwei Sichten, zwei Modelle.** `listByInvoice()` liefert `InvoiceTag`, `cloud()` liefert `InvoiceTagCloudEntry`. Verwechslung führt zu „warum hat das Ding kein `invoiceId`?“-Momenten.
- **Kein PUT.** Umbenennen geht nicht — alte Verknüpfung löschen, neue anlegen.
- **`name` ist case-sensitive.** `wichtig` und `Wichtig` sind aus Billomats Sicht zwei verschiedene Tags und erscheinen separat in der Tag-Cloud.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$invoiceId = 98765;

// 1) Tags anhängen
foreach (['wichtig', 'A-Kunde', '2026-Q2'] as $name) {
    $billomat->invoiceTags->create(
        new InvoiceTagCreateOptions(invoiceId: $invoiceId, name: $name),
    );
}

// 2) Tags der Rechnung listen
$tags = $billomat->invoiceTags->listByInvoice($invoiceId);
printf("Tags an Rechnung #%d: %s\n", $invoiceId, implode(', ', array_map(
    static fn ($t) => $t->name,
    $tags,
)));

// 3) Tag-Cloud — die 5 meistverwendeten Schlagworte
$cloud = $billomat->invoiceTags->cloud();
usort($cloud, static fn ($a, $b) => $b->count <=> $a->count);
foreach (array_slice($cloud, 0, 5) as $entry) {
    printf("%-20s %d\n", $entry->name, $entry->count);
}
```
