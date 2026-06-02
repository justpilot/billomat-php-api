# Properties (Custom-Field-Definitionen)

API-Wrapper für die vier Property-Definition-Endpoints unter `/article-properties`, `/client-properties`, `/supplier-properties` und `/incoming-properties`.

## Zugriff

```php
$billomat->articleProperties   // Definitionen für Artikel-Custom-Fields
$billomat->clientProperties    // Definitionen für Kunden-Custom-Fields
$billomat->supplierProperties  // Definitionen für Lieferanten-Custom-Fields
$billomat->incomingProperties  // Definitionen für Eingangsbeleg-Custom-Fields
```

## Modell

Eine *Property* ist ein benutzerdefiniertes Feld (Custom Field) auf einer der vier Hauptressourcen. Diese APIs verwalten ausschliesslich die **Definitionen** — also Name, Typ und optionalen Default-Wert eines solchen Feldes.

Die konkreten **Werte** an einer Instanz werden über separate Sub-Ressourcen gepflegt:

- Werte an einem Artikel: `articlePropertyValues` (siehe [Articles](articles.md))
- Werte an einem Kunden: `clientPropertyValues` (siehe [Clients](clients.md))
- Werte an einem Lieferanten: `supplierPropertyValues` (siehe [Suppliers](suppliers.md))
- Werte an einer Eingangsrechnung: `incomingPropertyValues` (siehe [Incomings](incomings.md))

Die vier Property-Definition-APIs sind strukturell identisch (gleiche Verben, gleiche Payload-Struktur). Sie unterscheiden sich nur im Endpoint-Pfad und im Read-Modell. Die Write-Klasse `PropertyCreateOptions` wird von allen vier APIs geteilt.

## Endpunkt-Übersicht

Alle vier APIs bieten dieselben Verben mit derselben Signatur. Pfad und Read-Modell variieren:

| Property-Klient | Endpoint-Basis | Read-Modell |
|---|---|---|
| `articleProperties` | `/article-properties` | `ArticleProperty` |
| `clientProperties` | `/client-properties` | `ClientProperty` |
| `supplierProperties` | `/supplier-properties` | `SupplierProperty` |
| `incomingProperties` | `/incoming-properties` | `IncomingProperty` |

### Verben (für jede der vier APIs)

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/{basis}` |
| `get($id)` | GET | `/{basis}/{id}` |
| `create($options)` | POST | `/{basis}` |
| `update($id, $options)` | PUT | `/{basis}/{id}` |
| `delete($id)` | DELETE | `/{basis}/{id}` |

## Methoden

Beispiele anhand von `articleProperties` — `clientProperties`, `supplierProperties` und `incomingProperties` verhalten sich exakt analog.

### `list(array $filters = []): list<TProperty>`

Listet alle Property-Definitionen. Filter werden 1:1 als Query-Parameter weitergereicht; Array-Werte werden als `key[]=…` codiert.

```php
$all = $billomat->articleProperties->list();

$paged = $billomat->clientProperties->list([
    'per_page' => 100,
    'order_by' => 'position+ASC',
]);
```

### `get(int $id): ?TProperty`

Liefert `null` bei 404.

```php
$prop = $billomat->supplierProperties->get(42);
if (null === $prop) {
    // Property gibt es nicht (mehr)
}
```

### `create(PropertyCreateOptions $options): TProperty`

Legt eine neue Property-Definition an. Pflicht ist nur `name`.

```php
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Model\Enum\PropertyType;

$opts = new PropertyCreateOptions(name: 'Lieferzeit');
$opts->type = PropertyType::TEXTFIELD;
$opts->defaultValue = '3 Werktage';
$opts->position = 10;

$prop = $billomat->articleProperties->create($opts);
```

### `update(int $id, PropertyCreateOptions $options): TProperty`

Aktualisiert eine bestehende Definition. Die Options-Klasse ist identisch mit `create()` — gesetzte Felder werden überschrieben, nicht gesetzte (`null`) bleiben unberührt.

```php
$opts = new PropertyCreateOptions(name: 'Lieferzeit');
$opts->defaultValue = '5 Werktage';

$billomat->articleProperties->update(42, $opts);
```

### `delete(int $id): bool`

Löscht die Definition. Bestehende Werte an Instanzen werden ebenfalls invalide.

```php
$billomat->incomingProperties->delete(42);
```

## Write-Modell: `PropertyCreateOptions`

Gemeinsame Options-Klasse für alle vier APIs, sowohl für `create()` als auch für `update()`. Konstruktor: `new PropertyCreateOptions(string $name)` — `name` ist Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `name` | `name` | `string` | Pflicht, Konstruktor-Argument |
| `type` | `type` | `?PropertyType` | `TEXTFIELD`, `TEXTAREA`, `CHECKBOX` |
| `defaultValue` | `default_value` | `?string` | Default-Wert für neue Instanzen |
| `position` | `position` | `?int` | Sortierung in der Billomat-UI |

`toArray()` entfernt `null`-Felder per `array_filter`. `name` wird stets mitgeschickt.

## Read-Modell: `ArticleProperty`

`final readonly class ArticleProperty`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `type` | `?PropertyType` |
| `defaultValue` | `?string` |
| `position` | `?int` |

## Read-Modell: `ClientProperty`

`final readonly class ClientProperty`. Strukturell identisch zu `ArticleProperty`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `type` | `?PropertyType` |
| `defaultValue` | `?string` |
| `position` | `?int` |

## Read-Modell: `SupplierProperty`

`final readonly class SupplierProperty`. Strukturell identisch zu `ArticleProperty`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `type` | `?PropertyType` |
| `defaultValue` | `?string` |
| `position` | `?int` |

## Read-Modell: `IncomingProperty`

`final readonly class IncomingProperty`. Strukturell identisch zu `ArticleProperty`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `name` | `string` |
| `type` | `?PropertyType` |
| `defaultValue` | `?string` |
| `position` | `?int` |

## Verwendete Enums

- [`PropertyType`](../../src/Model/Enum/PropertyType.php): `TEXTFIELD`, `TEXTAREA`, `CHECKBOX` — Wire-Werte sind grossgeschrieben (`TEXTFIELD`, nicht `textfield`).

## Stolpersteine

- **Definitionen vs. Werte nicht verwechseln.** `articleProperties` definiert nur das Schema („Es gibt ein Feld namens *Lieferzeit*"). Um einem konkreten Artikel den Wert `5 Werktage` zuzuweisen, ist `articlePropertyValues` zuständig.
- **Eine Options-Klasse für alle vier APIs.** `PropertyCreateOptions` lebt im Namespace `Justpilot\Billomat\Api` und wird mehrfach verwendet. Beim Refactoring nicht versehentlich pro Ressource duplizieren.
- **`type` ist optional.** Wird `type` weggelassen, behandelt Billomat das Feld als Freitext. Für `CHECKBOX`-Felder ist `defaultValue` ein String — typische Wire-Werte sind `"0"` und `"1"`.
- **`position` steuert nur die UI-Sortierung.** Es ist kein Eindeutigkeits-Constraint; Duplikate sind zulässig.
- **Löschen ist destruktiv.** Wer eine Definition löscht, verliert auch die bisher daran gespeicherten Werte an den Instanzen. Für „nur ausblenden" gibt es keinen API-Weg — die Anwendungsebene muss das selbst modellieren.
- **`update()` nimmt dieselbe Klasse wie `create()`.** Es gibt keinen separaten `PropertyUpdateOptions`-Typ. Bei einem PUT ohne `type` lässt Billomat den bisherigen Typ stehen.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PropertyType;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) Custom-Field-Definition für Artikel anlegen
$opts = new PropertyCreateOptions(name: 'Lieferzeit');
$opts->type = PropertyType::TEXTFIELD;
$opts->defaultValue = '3 Werktage';
$opts->position = 10;

$leadTimeDef = $billomat->articleProperties->create($opts);
printf("Definition #%d angelegt: %s\n", $leadTimeDef->id, $leadTimeDef->name);

// 2) Bestand inspizieren
foreach ($billomat->articleProperties->list(['per_page' => 100]) as $def) {
    printf(
        "#%d %-20s [%s] default=%s\n",
        $def->id,
        $def->name,
        $def->type?->value ?? 'FREE',
        $def->defaultValue ?? '-',
    );
}

// 3) Default-Wert nachträglich erhöhen
$update = new PropertyCreateOptions(name: 'Lieferzeit');
$update->defaultValue = '5 Werktage';
$billomat->articleProperties->update($leadTimeDef->id, $update);

// 4) Parallele Definition für Kunden — selbe API-Form, andere Ressource
$vipFlag = $billomat->clientProperties->create(
    (function (): PropertyCreateOptions {
        $o = new PropertyCreateOptions(name: 'VIP');
        $o->type = PropertyType::CHECKBOX;
        $o->defaultValue = '0';

        return $o;
    })(),
);

// 5) Werte (nicht Definitionen!) pflegt eine andere API:
//    $billomat->articlePropertyValues->create(...)
//    $billomat->clientPropertyValues->create(...)
```
