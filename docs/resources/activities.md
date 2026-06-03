<!-- Quelle: https://www.billomat.com/api/aktivitaeten/ -->

# Activities (Aktivitäten / Aktivitätsfeed)

API-Wrapper für den Aktivitätsfeed unter `/activity-feed`.

## Zugriff

```php
$billomat->activities
```

`Justpilot\Billomat\Api\ActivitiesApi`.

## Überblick

Billomat protokolliert in einem zentralen Feed jede Veränderung an den eigenen Datensätzen — Statuswechsel an Rechnungen, Mailversand, Erstellen von Belegen, Zahlungsbuchungen. Der Feed ist read-only: Aktivitäten entstehen ausschließlich als Seiteneffekt anderer Operationen.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/activity-feed` |
| `listPage($filters?)` | GET | `/activity-feed?page=N&per_page=M` |
| `iterateAll($filters?, $pageSize?)` | GET (lazy) | `/activity-feed?page=…` |

Pagination funktioniert über `page` und `per_page` wie überall im SDK.

## Methoden

### `list(array $filters = []): list<Activity>`

Holt eine Seite. Filter werden als Query-String mitgegeben.

```php
$activities = $billomat->activities->list([
    'page' => 1,
    'per_page' => 50,
]);

foreach ($activities as $a) {
    echo "[{$a->date?->format('c')}] {$a->resource}#{$a->id}: {$a->title}\n";
}
```

### `listPage(array $filters = []): Page<Activity>`

Einzelne Seite samt `PageInfo` (`page`, `perPage`, `total`).

### `iterateAll(array $filters = [], int $pageSize = 100): Generator<int, Activity>`

Lazy über den gesamten Feed, seitenweise pro `pageSize`-Items. Stoppt selbsttätig bei der letzten Seite.

```php
foreach ($billomat->activities->iterateAll() as $activity) {
    if ($activity->isSystemActivity()) {
        continue;
    }
    // ... nur Benutzer-Aktivitäten
}
```

## Modell `Activity`

`Justpilot\Billomat\Model\Activity` — `final readonly`.

| Feld | Typ | Beschreibung |
|---|---|---|
| `resource` | `string` | Slug der Ressource (`invoices`, `delivery-notes`, `clients`, …) |
| `id` | `?int` | ID des betroffenen Datensatzes innerhalb der Ressource |
| `date` | `?DateTimeImmutable` | Zeitpunkt der Aktivität |
| `title` | `?string` | Überschrift, z. B. `"Rechnung RE123"` |
| `text` | `?string` | Freitext, z. B. `"Status geändert von Entwurf nach offen."` |
| `userId` | `?int` | Auslösender Benutzer; `null` für System-Aktivitäten |

Hilfsmethode `isSystemActivity(): bool` für `null === userId`.

## Stolpersteine

- **`user_id` kann leerer String sein.** Bei System-Aktivitäten liefert Billomat `<user_id></user_id>` — im JSON als `""`. `Activity::fromArray()` normalisiert das via `ScalarCaster::toIntOrNull()` zu `null`.
- **Slug, nicht Klassen-Name.** `resource` ist der URL-Pfad-Slug, also `"delivery-notes"` (mit Bindestrich), nicht `"deliveryNotes"`.
- **Read-only, kein `get()`.** Es gibt keinen Einzel-Endpoint `/activity-feed/{id}` — wer einen einzelnen Datensatz braucht, muss die Ressource direkt abfragen (`$billomat->invoices->get($a->id)`).
- **Schema-Drift möglich.** Billomat liefert in `resource` neue Werte, wenn Features hinzukommen; daher bewusst kein Enum.
- **Server-seitige Filter.** Die Doku nennt keine Filter-Parameter explizit, aber `page`/`per_page` funktionieren. Resource-Filter müssen ggf. clientseitig erfolgen.

## End-to-End

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'secret');

// Alle Aktivitäten der letzten Iteration sammeln
foreach ($billomat->activities->iterateAll() as $activity) {
    if ($activity->resource !== 'invoices' || $activity->isSystemActivity()) {
        continue;
    }
    // ... Audit-Log
}
```
