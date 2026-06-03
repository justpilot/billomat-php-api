<!-- Quelle: https://www.billomat.com/api/einstellungen/mahnstufen/ -->

# Reminder Levels / Dunning Levels (Mahnstufen)

Mahnstufen werden in Billomat über **zwei** verwandte, aber technisch getrennte Endpunkte abgebildet. Das SDK exponiert beide:

| API | Endpunkt | Inhalt |
|---|---|---|
| `ReminderTextsApi` | `/reminder-texts` | Text-Bausteine pro Mahnstufe (Betreff, Header, Footer, Gebühren) — entspricht der offiziell dokumentierten *Mahnstufen*-Seite. |
| `DunningLevelsApi` | `/dunning-levels` | Numerische Metadaten der Mahnstufe (Position, Fälligkeitstage, Gebühr, Zinsen). Nicht von allen Tenants ausgeliefert. |

Beide Endpunkte sind read-only im SDK.

## Zugriff

```php
$billomat->reminderTexts   // Justpilot\Billomat\Api\ReminderTextsApi
$billomat->dunningLevels   // Justpilot\Billomat\Api\DunningLevelsApi
```

## Endpunkt-Übersicht

### `ReminderTextsApi`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/reminder-texts` |
| `get($id)` | GET | `/reminder-texts/{id}` |

### `DunningLevelsApi`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/dunning-levels` |
| `get($id)` | GET | `/dunning-levels/{id}` |

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

### `DunningLevelsApi::list(array $filters = []): list<DunningLevel>`

### `DunningLevelsApi::get(int $id): ?DunningLevel`

## Read-Modelle

### `ReminderText`

`final readonly class ReminderText`.

| Property | Billomat-Feld | Typ | Zweck |
|---|---|---|---|
| `id` | `id` | `?int` | |
| `name` | `name` | `?string` | Name der Mahnstufe (intern) |
| `subject` | `subject` | `?string` | Betreff der Mahn-E-Mail/des Briefs |
| `header` | `header` | `?string` | Einleitung |
| `footer` | `footer` | `?string` | Anmerkung |
| `dueDays` | `due_days` | `?int` | Fälligkeitstage (SDK-spezifisch — nicht in der Spec dokumentiert) |
| `sort` | `sort` | `?int` | Reihenfolge der Stufe |

> Die Billomat-Spec listet zusätzlich `sorting` (statt `sort`) und `charge_name`/`charge_description`/`charge_amount` für Mahngebühren. Das SDK exponiert diese nicht im Read-Modell — Erweiterung in `ReminderText::fromArray()` ist trivial, wenn benötigt.

### `DunningLevel`

`final readonly class DunningLevel`.

| Property | Billomat-Feld | Typ | Zweck |
|---|---|---|---|
| `id` | `id` | `?int` | |
| `name` | `name` | `?string` | Name der Stufe |
| `position` | `position` | `?int` | Position in der Mahn-Reihenfolge |
| `dueDays` | `due_days` | `?int` | Fälligkeitstage bis zur nächsten Stufe |
| `charge` | `charge` | `?float` | Mahngebühr |
| `interest` | `interest` | `?float` | Verzugszinsen (Prozent) |

## Welche der beiden API nutzen?

- **`ReminderTextsApi`** wenn der Text der Mahnung interessiert (Betreff, Einleitung, Footer) — z.B. um Vorlagen zu prüfen oder UI-Anzeigen zu bauen.
- **`DunningLevelsApi`** wenn die numerische Konfiguration interessiert (nach wievielen Tagen die Stufe greift, mit welcher Gebühr/Zinsen).
- Beide gemeinsam, wenn ein vollständiges Bild einer Mahnstufe benötigt wird — die ID ist allerdings **nicht** über beide Endpunkte verknüpft; der Mapping müsste über `name`/`position` erfolgen.

## Stolpersteine

- **`/dunning-levels` nicht auf allen Tenants verfügbar.** Der Integration-Test (`tests/Integration/Lookups/LookupsIntegrationTest.php`) markiert sich als `skipped`, wenn Billomat 404 zurückgibt.
- **Spec-Drift im Read-Modell.** Wie oben erwähnt — `ReminderText` exponiert `sort` (das Billomat-Feld heisst `sorting`) und kennt die `charge_*`-Felder nicht. Wer Mahngebühren aus der API lesen muss, ergänzt `ReminderText`.
- **Single-Item-List-Quirk.** Beide APIs normalisieren über `listResource()`.

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

// Mahn-Texte in Reihenfolge
$texts = $billomat->reminderTexts->list();
usort($texts, static fn ($a, $b) => ($a->sort ?? 0) <=> ($b->sort ?? 0));

foreach ($texts as $rt) {
    printf("Stufe %d: %s\n  Betreff: %s\n",
        $rt->sort ?? 0,
        $rt->name ?? '',
        $rt->subject ?? '',
    );
}

// Numerische Konfiguration (falls Tenant unterstützt)
try {
    foreach ($billomat->dunningLevels->list() as $level) {
        printf("[Pos %d] %s — %d Tage, %.2f € Gebühr, %.1f %% Zinsen\n",
            $level->position ?? 0,
            $level->name ?? '',
            $level->dueDays ?? 0,
            $level->charge ?? 0.0,
            $level->interest ?? 0.0,
        );
    }
} catch (\Justpilot\Billomat\Exception\NotFoundException) {
    echo "Sandbox stellt /dunning-levels nicht bereit.\n";
}
```
