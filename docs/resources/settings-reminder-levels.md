<!-- Quelle: https://www.billomat.com/api/einstellungen/mahnstufen/ -->

# Reminder Levels (Mahnstufen)

Mahnstufen werden in Billomat über `/reminder-texts` abgebildet. Das SDK exponiert dafür `ReminderTextsApi`:

| API | Endpunkt | Inhalt |
|---|---|---|
| `ReminderTextsApi` | `/reminder-texts` | Text-Bausteine pro Mahnstufe (Betreff, Header, Footer, Gebühren) — entspricht der offiziell dokumentierten *Mahnstufen*-Seite. |

Der Endpunkt ist im SDK read-only.

## Zugriff

```php
$billomat->reminderTexts   // Justpilot\Billomat\Api\ReminderTextsApi
```

## Endpunkt-Übersicht

### `ReminderTextsApi`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/reminder-texts` |
| `get($id)` | GET | `/reminder-texts/{id}` |

> Anlegen/Bearbeiten/Löschen sind bei Billomat verfügbar (POST/PUT/DELETE auf `/reminder-texts`), aber nicht als SDK-Methoden exponiert.

## Methoden

### `ReminderTextsApi::list(array $filters = []): list<ReminderText>`

```php
foreach ($billomat->reminderTexts->list() as $rt) {
    printf("Stufe %d: %s — '%s'\n",
        $rt->sort ?? 0,
        $rt->name ?? '(ohne Name)',
        $rt->subject ?? '',
    );
}
```

### `ReminderTextsApi::get(int $id): ?ReminderText`

Liefert `null` bei 404.

## Read-Modell

### `ReminderText`

`final readonly class ReminderText`.

| Property | Billomat-Feld | Typ | Zweck |
|---|---|---|---|
| `id` | `id` | `?int` | |
| `name` | `name` | `?string` | Name der Mahnstufe (intern) |
| `subject` | `subject` | `?string` | Betreff der Mahn-E-Mail/des Briefs |
| `header` | `header` | `?string` | Einleitung |
| `footer` | `footer` | `?string` | Anmerkung |
| `sort` | `sorting` (Fallback `sort`) | `?int` | Reihenfolge der Stufe |
| `chargeName` | `charge_name` | `?string` | Name der Mahngebühr |
| `chargeDescription` | `charge_description` | `?string` | Beschreibung der Mahngebühr |
| `chargeAmount` | `charge_amount` | `?Money` | Höhe der Mahngebühr |
| `customfield` | `customfield` | `?string` | Freies Zusatzfeld, siehe [Custom-Meta-Attribute](../concepts/custom-meta-attributes.md) |

## Stolpersteine

- **`sort` statt `sorting`.** Billomat liefert die Reihenfolge als `sorting` (früher `sort`); das SDK stellt sie weiterhin als `ReminderText::$sort` bereit, und `toArray()` schreibt sie unter dem Schlüssel `sort` zurück.
- **Mahnstufe einer Mahnung.** `Reminder::$reminderLevel` und `Reminder::$reminderLevelName` verweisen auf die hier gepflegten Stufen, siehe [Reminders](reminders.md).
- **Single-Item-List-Quirk.** `list()` normalisiert über `listResource()`, auch wenn Billomat nur eine einzelne Stufe als Objekt statt als Liste liefert.

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

// Mahnstufen in Reihenfolge
$texts = $billomat->reminderTexts->list();
usort($texts, static fn ($a, $b) => ($a->sort ?? 0) <=> ($b->sort ?? 0));

foreach ($texts as $rt) {
    printf("Stufe %d: %s\n  Betreff: %s\n  Gebühr: %s € (%s)\n",
        $rt->sort ?? 0,
        $rt->name ?? '',
        $rt->subject ?? '',
        $rt->chargeAmount?->format(2, ',', '.') ?? '0,00',
        $rt->chargeName ?? 'keine',
    );
}
```
