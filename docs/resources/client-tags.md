<!-- Quelle: https://www.billomat.com/api/kunden/schlagworte/ -->

# Client Tags (Kunden-Schlagworte)

API-Wrapper für Schlagworte an Kunden unter `/client-tags`. Konzeptionell identisch zu [Invoice Tags](invoice-tags.md) — wer das Modell dort verstanden hat, kann diese Datei als Spickzettel lesen.

## Zugriff

```php
$billomat->clientTags
```

`Justpilot\Billomat\Api\ClientTagsApi`.

## Überblick

Wie bei `invoice-tags` gibt es zwei Sichten unter derselben Ressource:

- **Tags eines Kunden** — über `?client_id={id}`. Response-Element: `client-tag`.
- **Tag-Cloud** — alle Tags des Accounts mit Häufigkeit, ohne Filter. Response-Element: `tag` (anderer Key!).

Die zwei Sichten werden auf zwei Methoden mit zwei separaten Read-Modellen abgebildet.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `listByClient($clientId)` | GET | `/client-tags?client_id={id}` |
| `cloud()` | GET | `/client-tags` |
| `get($id)` | GET | `/client-tags/{id}` |
| `create($options)` | POST | `/client-tags` |
| `delete($id)` | DELETE | `/client-tags/{id}` |

Es gibt keine `update()`-Methode — ein Tag wird gelöscht und neu angelegt.

## Methoden

### `listByClient(int $clientId): list<ClientTag>`

Listet die Schlagworte eines einzelnen Kunden.

```php
$tags = $billomat->clientTags->listByClient(98765);

foreach ($tags as $tag) {
    echo $tag->name . "\n";
}
```

### `cloud(): list<ClientTagCloudEntry>`

Aggregierte Liste aller Kunden-Tags im Account mit `count`-Häufigkeit. Nützlich für Autovervollständigung im UI oder eine Tag-Cloud-Anzeige.

```php
foreach ($billomat->clientTags->cloud() as $entry) {
    printf("%s (%d Kunden)\n", $entry->name, $entry->count);
}
```

### `get(int $id): ?ClientTag`

Liefert `null` bei 404.

### `create(ClientTagCreateOptions $options): ClientTag`

```php
use Justpilot\Billomat\Api\ClientTagCreateOptions;

$tag = $billomat->clientTags->create(
    new ClientTagCreateOptions(clientId: 98765, name: 'A-Kunde'),
);
```

Mehrere Tags für denselben Kunden → mehrere `create()`-Aufrufe.

### `delete(int $id): bool`

Entfernt die Verknüpfung zwischen Kunde und Tag. Wird das Schlagwort an keinem Kunden mehr verwendet, verschwindet es automatisch aus der Tag-Cloud.

## Write-Modell: `ClientTagCreateOptions`

Konstruktor: `new ClientTagCreateOptions(int $clientId, string $name)`. Beide Werte sind Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | Pflicht |
| `name` | `name` | `string` | Pflicht, Freitext |

`toArray()` strippt hier nichts — beide Felder sind immer gesetzt.

## Read-Modell: `ClientTag`

`final readonly class ClientTag` — eine konkrete Tag-Verknüpfung an einem Kunden.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `name` | `string` |

## Read-Modell: `ClientTagCloudEntry`

`final readonly class ClientTagCloudEntry` — ein aggregierter Eintrag aus `cloud()`. Hat bewusst **kein** `clientId`-Feld, weil der Eintrag mehrere Kunden aggregiert.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `count` | `int` |

## Verwendete Enums

Keine. `name` ist freitextlich.

## Stolpersteine

- **Zwei Sichten, zwei Modelle.** `listByClient()` liefert `ClientTag`, `cloud()` liefert `ClientTagCloudEntry`. Verwechslung führt zu „warum hat das Ding kein `clientId`?“-Momenten.
- **Inkonsistente Response-Keys.** `listByClient()` parst `client-tags.client-tag`, `cloud()` parst `client-tags.tag` — derselbe Endpunkt, zwei verschiedene innere Keys, je nach Vorhandensein des `client_id`-Filters. Das SDK kapselt das, beim Debuggen der Roh-Response aber wichtig zu wissen.
- **Single-Item-List-Quirk.** Hat ein Kunde nur ein Tag (bzw. enthält die Cloud nur einen Eintrag), liefert Billomat ein einzelnes Objekt statt einer Liste. Beide Methoden normalisieren das via `isset($rows['id'])`- bzw. `isset($rows['name'])`-Check.
- **Kein PUT.** Umbenennen geht nicht — alte Verknüpfung löschen, neue anlegen.
- **`name` ist case-sensitive.** `a-kunde` und `A-Kunde` sind aus Billomats Sicht zwei verschiedene Tags und erscheinen separat in der Tag-Cloud.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ClientTagCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$clientId = 98765;

// 1) Tags an einen Kunden anhängen
foreach (['A-Kunde', 'Stammkunde', '2026-Q2'] as $name) {
    $billomat->clientTags->create(
        new ClientTagCreateOptions(clientId: $clientId, name: $name),
    );
}

// 2) Tags des Kunden listen
$tags = $billomat->clientTags->listByClient($clientId);
printf("Tags an Kunde #%d: %s\n", $clientId, implode(', ', array_map(
    static fn ($t) => $t->name,
    $tags,
)));

// 3) Tag-Cloud — die 5 meistverwendeten Schlagworte
$cloud = $billomat->clientTags->cloud();
usort($cloud, static fn ($a, $b) => $b->count <=> $a->count);
foreach (array_slice($cloud, 0, 5) as $entry) {
    printf("%-20s %d\n", $entry->name, $entry->count);
}
```
