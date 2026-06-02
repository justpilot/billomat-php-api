# Contacts (Ansprechpartner)

API-Wrapper für Kunden-Ansprechpartner unter `/contacts`. Ein Contact gehört immer zu genau einem Kunden (`client_id` als Fremdschlüssel). Typischer Anwendungsfall: pro Firma mehrere Kontaktpersonen (Buchhaltung, Einkauf, Geschäftsführung) hinterlegen.

## Zugriff

```php
$billomat->contacts
```

`Justpilot\Billomat\Api\ContactsApi`.

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `listByClient($clientId, $query?)` | GET | `/contacts?client_id={id}` |
| `get($id)` | GET | `/contacts/{id}` |
| `create($options)` | POST | `/contacts` |
| `update($id, $options)` | PUT | `/contacts/{id}` |
| `delete($id)` | DELETE | `/contacts/{id}` |

Es gibt **keine** ungefilterte `list()`-Methode — Billomats `GET /contacts` verlangt zwingend einen `client_id`-Filter. Das SDK bildet das direkt in der Signatur ab.

## Methoden

### `listByClient(int $clientId, array $query = []): list<Contact>`

Listet alle Ansprechpartner eines Kunden.

```php
$contacts = $billomat->contacts->listByClient(98765);

foreach ($contacts as $contact) {
    printf("#%d %s %s <%s>\n",
        $contact->id,
        $contact->firstName ?? '',
        $contact->lastName ?? '',
        $contact->email ?? '—',
    );
}
```

`$query` erlaubt zusätzliche Parameter wie `page` und `per_page`.

### `get(int $id): ?Contact`

Liefert `null` bei 404.

### `create(ContactCreateOptions $options): Contact`

```php
use Justpilot\Billomat\Api\ContactCreateOptions;

$opts = new ContactCreateOptions(clientId: 98765);
$opts->firstName = 'Erika';
$opts->lastName = 'Mustermann';
$opts->email = 'erika@example.com';

$created = $billomat->contacts->create($opts);
```

`clientId` ist Pflicht und Konstruktor-Argument; alle anderen Felder sind optional und werden als Properties gesetzt.

### `update(int $id, ContactUpdateOptions $options): Contact`

```php
use Justpilot\Billomat\Api\ContactUpdateOptions;

$patch = new ContactUpdateOptions();
$patch->email = 'neue.adresse@example.com';

$billomat->contacts->update($contactId, $patch);
```

`ContactUpdateOptions` ist ein echtes Partial-Update: nur gesetzte (nicht-`null`) Properties werden gesendet. Die `client_id` ist beim Update bewusst nicht änderbar.

### `delete(int $id): bool`

Entfernt den Ansprechpartner. Gibt `true` zurück, wenn der Aufruf erfolgreich war.

## Write-Modell: `ContactCreateOptions`

Konstruktor: `new ContactCreateOptions(int $clientId)`. Alle weiteren Felder sind public-Properties und nullable.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `clientId` | `client_id` | `int` | Pflicht, Konstruktor-Argument |
| `label` | `label` | `?string` | Frei wählbares Label (z. B. `Buchhaltung`) |
| `salutation` | `salutation` | `?string` | Anrede (`Herr`, `Frau`, …) |
| `firstName` | `first_name` | `?string` | |
| `lastName` | `last_name` | `?string` | |
| `street` | `street` | `?string` | |
| `zip` | `zip` | `?string` | |
| `city` | `city` | `?string` | |
| `state` | `state` | `?string` | Bundesland/Region |
| `countryCode` | `country_code` | `?string` | ISO-3166-1 alpha-2 (z. B. `DE`) |
| `email` | `email` | `?string` | |
| `phone` | `phone` | `?string` | |
| `fax` | `fax` | `?string` | |
| `mobile` | `mobile` | `?string` | |
| `note` | `note` | `?string` | Freitext-Notiz |

`toArray()` strippt `null`-Werte mit `array_filter` — Felder, die du nicht setzt, landen nicht im Wire-Format.

## Write-Modell: `ContactUpdateOptions`

Funktional identisch zu `ContactCreateOptions`, **ohne** `clientId`. Beim Update lässt Billomat die Kundenzugehörigkeit nicht ändern. Alle Felder sind optionale Properties, und `toArray()` strippt `null`-Werte ebenfalls.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `label` | `label` | `?string` | |
| `salutation` | `salutation` | `?string` | |
| `firstName` | `first_name` | `?string` | |
| `lastName` | `last_name` | `?string` | |
| `street` | `street` | `?string` | |
| `zip` | `zip` | `?string` | |
| `city` | `city` | `?string` | |
| `state` | `state` | `?string` | |
| `countryCode` | `country_code` | `?string` | |
| `email` | `email` | `?string` | |
| `phone` | `phone` | `?string` | |
| `fax` | `fax` | `?string` | |
| `mobile` | `mobile` | `?string` | |
| `note` | `note` | `?string` | |

## Read-Modell: `Contact`

`final readonly class Contact`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `clientId` | `int` |
| `created` | `?DateTimeImmutable` |
| `label` | `?string` |
| `salutation` | `?string` |
| `firstName` | `?string` |
| `lastName` | `?string` |
| `street` | `?string` |
| `zip` | `?string` |
| `city` | `?string` |
| `state` | `?string` |
| `countryCode` | `?string` |
| `email` | `?string` |
| `phone` | `?string` |
| `fax` | `?string` |
| `mobile` | `?string` |
| `note` | `?string` |

`created` wird aus dem Billomat-String mittels `new DateTimeImmutable(...)` geparst; bei ungültigem Wert bleibt das Feld `null`.

## Verwendete Enums

Keine. Alle Felder sind Strings, einschließlich `salutation` und `countryCode` — letzteres erwartet Billomat als ISO-3166-1-alpha-2-Code (Validierung erfolgt serverseitig).

## Stolpersteine

- **`listByClient()` ist ein Pflichtfilter.** Billomats `GET /contacts` ohne `client_id` liefert keine globale Liste, sondern einen Fehler. Das SDK macht den Filter zum erzwungenen Konstruktor-Argument.
- **Single-Item-List-Quirk.** Hat ein Kunde genau einen Ansprechpartner, liefert Billomat ein einzelnes Objekt unter `contacts.contact` statt einer Liste. `listByClient()` normalisiert das via `array_is_list()`-Check zu einer `list<Contact>`.
- **`client_id` ist beim Update nicht änderbar.** `ContactUpdateOptions` enthält das Feld bewusst nicht. Wer einen Ansprechpartner zu einem anderen Kunden „verschieben“ will, muss ihn löschen und neu anlegen.
- **`toArray()` strippt `null`.** Ein Feld auf `null` setzen, um es per Update zu **leeren**, funktioniert nicht — das Feld wird ausgelassen und Billomat behält den alten Wert. Zum Leeren einen leeren String `''` senden.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\ContactCreateOptions;
use Justpilot\Billomat\Api\ContactUpdateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$clientId = 98765;

// 1) Bestehende Ansprechpartner listen
foreach ($billomat->contacts->listByClient($clientId) as $contact) {
    printf("[#%d] %s %s — %s\n",
        $contact->id,
        $contact->firstName ?? '',
        $contact->lastName ?? '',
        $contact->email ?? '—',
    );
}

// 2) Neuen Ansprechpartner anlegen
$opts = new ContactCreateOptions(clientId: $clientId);
$opts->label = 'Buchhaltung';
$opts->salutation = 'Frau';
$opts->firstName = 'Erika';
$opts->lastName = 'Mustermann';
$opts->email = 'buchhaltung@example.com';
$opts->countryCode = 'DE';

$created = $billomat->contacts->create($opts);
printf("Angelegt: #%d %s %s\n", $created->id, $created->firstName, $created->lastName);

// 3) E-Mail-Adresse aktualisieren
$patch = new ContactUpdateOptions();
$patch->email = 'finance@example.com';

$updated = $billomat->contacts->update($created->id, $patch);
var_dump($updated->email); // 'finance@example.com'

// 4) Wieder löschen
$billomat->contacts->delete($created->id);
```
