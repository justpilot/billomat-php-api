<!-- Quelle: https://www.billomat.com/api/grundlagen/authentifizierung/ -->

# Authentifizierung

Jeder API-Aufruf wird über einen persönlichen **API-Schlüssel** authentifiziert, der einem konkreten Billomat-Benutzer gehört. Die API ist zustandslos — es gibt keine Sessions, der Schlüssel muss bei jedem Request mitgeschickt werden.

## API-Schlüssel erzeugen

1. Im Billomat-UI unter *Einstellungen → Mitarbeiter* den Benutzer öffnen und **API-Zugriff freischalten**.
2. Anschließend lässt sich für diesen Benutzer ein API-Schlüssel generieren.

Der Schlüssel wirkt als Passwort: Wer ihn besitzt, kann mit den Rechten des zugeordneten Benutzers auf die API zugreifen.

## Übertragung

Billomat akzeptiert den Schlüssel auf zwei Wegen:

| Weg | Form | Empfehlung |
|---|---|---|
| HTTP-Header | `X-BillomatApiKey: <key>` | Standard — in Logs leichter zu filtern als URLs, kein Risiko durch Referer-Header. |
| Query-Parameter | `?api_key=<key>` | Nur für ad-hoc-Tests; landet in Server-Logs und Browser-History. |

Dieses SDK verwendet ausschließlich den **Header-Weg** (siehe `src/Http/BillomatHttpClient.php`).

## Apps registrieren (`X-AppId`, `X-AppSecret`)

Wenn das aufrufende System in Billomat unter *Einstellungen → Administration → Apps* als eigene App registriert wurde, sollten zusätzlich die App-Header gesendet werden:

| Header | Bedeutung |
|---|---|
| `X-AppId` | ID der registrierten App |
| `X-AppSecret` | Geheimer Schlüssel der App |

Ohne diese Header werden Aufrufe als „unregistrierte App" gezählt und teilen sich das geringere Tarif-Kontingent (siehe [Errors & Rate-Limits](errors-and-rate-limits.md)).

## Konfiguration im SDK

`BillomatConfig::credentials()` nimmt alle drei Werte entgegen:

```php
$client = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: '…',       // optional
    appSecret: '…',   // optional
);
```

`BillomatHttpClient` setzt daraus pro Request:

- `X-BillomatApiKey: <apiKey>`
- `X-AppId: <appId>` (falls gesetzt)
- `X-AppSecret: <appSecret>` (falls gesetzt)

## Sicherheitshinweise

- API-Schlüssel **niemals** in versionierten Code commiten — `.env.test.local` ist gitignored, und produktive Schlüssel gehören in einen Secret-Store.
- Aus demselben Grund: keine Schlüssel in URL-Parametern (Server-/Proxy-Logs).
- Immer über HTTPS (siehe [API-Sicherheit](api-security.md)).
- Wer Read-Only-Zugriff braucht, bekommt einen separaten Benutzer mit eingeschränkten Rollen — der Schlüssel erbt dessen Rechte.
