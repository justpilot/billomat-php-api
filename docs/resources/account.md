<!-- Quelle: https://www.billomat.com/api/account/ -->

# Account (Eigene Account-Informationen)

API-Wrapper für die eigenen Account-Daten unter `/clients/myself`.

## Zugriff

```php
$billomat->account
```

`Justpilot\Billomat\Api\AccountApi`.

## Überblick

Billomat führt den eigenen Account technisch als Spezial-Client. Statt eines eigenen `/account`-Endpunkts wird die Kunden-Schnittstelle mit der reservierten ID `myself` aufgerufen. Im Unterschied zur normalen `GET /clients/{id}` enthält die Antwort zusätzlich den aktuellen **Tarif** (`plan`) und das **Kontingent** (`quotas`).

## Endpunkt-Übersicht

| Methode | HTTP | Pfad |
|---|---|---|
| `get(): Account` | GET | `/clients/myself` |

Es gibt keinen Write-Pfad — der eigene Account wird ausschließlich gelesen.

## Methode

### `get(): Account`

Liefert die eigenen Account-Informationen als `Account`-Modell.

```php
$account = $billomat->account->get();

echo $account->plan;           // z.B. "XL"
echo $account->client->name;   // Firmenname
echo $account->client->email;  // Rechnungs-E-Mail

foreach ($account->quotas as $quota) {
    printf(
        "%s: %d von %s verbraucht%s\n",
        $quota->entity,
        $quota->used,
        $quota->isUnlimited() ? '∞' : (string) $quota->available,
        $quota->isUnlimited() ? ' (unbegrenzt)' : '',
    );
}
```

Per Convenience auch direkt nach Entität abfragbar:

```php
$storage = $account->quota('storage');

if ($storage !== null && !$storage->isUnlimited()) {
    $percent = $storage->used / $storage->available * 100;
    // ...
}
```

## Modell `Account`

`Justpilot\Billomat\Model\Account` — komponiert das bestehende `Client`-Read-Modell mit den Account-spezifischen Feldern.

| Feld | Typ | Beschreibung |
|---|---|---|
| `client` | `Client` | Vollständiger Kunden-Datensatz (Stammdaten, Adresse, Bankverbindung etc.) |
| `plan` | `?string` | Tarifname, z.B. `"XL"`, `"S"`, `"Free"` |
| `quotas` | `list<AccountQuota>` | Kontingent-Liste, pro Entität ein Eintrag |

## Modell `AccountQuota`

`Justpilot\Billomat\Model\AccountQuota` — ein Kontingent-Eintrag.

| Feld | Typ | Beschreibung |
|---|---|---|
| `entity` | `string` | Bekannte Werte: `documents`, `clients`, `articles`, `storage` |
| `available` | `int` | Verfügbares Volumen; `-1` markiert „unbegrenzt" |
| `used` | `int` | Verbrauchtes Volumen (bei `storage` in Bytes) |

Hilfsmethode `isUnlimited(): bool` für die `available === -1`-Konvention.

## Stolpersteine

- **Kein `/account`-Endpunkt.** Wer den Account-Datensatz allein aus dem Pfad herleiten will, irrt — der Pfad ist `/clients/myself`.
- **Quota-Liste kann fehlen.** Bei manchen Tarifen liefert Billomat `quotas` gar nicht — dann ist die Liste leer und `quota($entity)` gibt `null` zurück.
- **`storage` in Bytes, alles andere als Stück.** `available`/`used` haben keine Einheit im Response — bei `storage` sind die Werte in Bytes (Beispiel aus der Doku: `8_740_060` Bytes), bei `documents`/`clients`/`articles` Stück.
- **`Account` ist kein `Client`.** Bewusst keine Vererbung — wer das volle Kunden-Modell für den eigenen Account braucht, greift auf `$account->client` zu. Alternativ liefert `$billomat->clients->getMyself()` direkt einen `Client` (ohne Plan/Quotas).

## End-to-End

```php
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: 'secret');

$account = $billomat->account->get();

if ($account->plan === 'Free') {
    $documents = $account->quota('documents');
    if ($documents !== null && $documents->used / $documents->available > 0.9) {
        // Upgrade nahelegen
    }
}
```
