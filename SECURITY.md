# Sicherheit

## Umgang mit API-Zugangsdaten

Der Billomat-API-Schlüssel hat denselben Lese- und Schreibzugriff wie der angemeldete User. Behandle ihn entsprechend wie ein Passwort.

- **Niemals committen.** Lege Credentials nicht in `composer.json`, README, Tests oder anderen versionierten Dateien ab. Für lokale Tests verwendet das SDK `.env.test.local`, das bereits in `.gitignore` steht.
- **Aus der Umgebung laden.** Empfohlen ist das Lesen aus Umgebungsvariablen (`getenv('BILLOMAT_API_KEY')`) oder einem Secret-Manager (Vault, AWS Secrets Manager, Doppler, etc.). Beispiele unter `examples/` zeigen das Muster.
- **Pro Umgebung trennen.** Verwende getrennte Keys für Sandbox, Staging und Produktion. Rotiere Keys nach personellen Wechseln.
- **`X-AppId` / `X-AppSecret` nur bei Bedarf.** Diese Header sind ausschließlich für eingetragene Drittanbieter-Apps relevant. Ein normaler Billomat-Account benötigt nur den API-Key (`X-BillomatApiKey`). Das SDK sendet App-Header nur, wenn beide Werte gesetzt sind.

## Logging und Fehler-Output

`HttpException` (und die Subklassen `AuthenticationException`, `NotFoundException`, `ValidationException`) speichern den unveränderten Antwort-Body der Billomat-API in `getResponseBody()`. Er ist beim Debuggen wertvoll, kann aber je nach Antwort sensible Kundendaten enthalten. Achte beim Loggen darauf:

- den Response-Body nicht ungefiltert in öffentliche Logs zu schreiben,
- den API-Key nicht versehentlich in Stack-Traces oder Request-Dumps auftauchen zu lassen — das SDK schreibt ihn nur in den `X-BillomatApiKey`-Header, nicht in URLs.

## Unterstützte Versionen

Aktuell wird ausschließlich die `1.x`-Linie aktiv gepflegt. Sicherheitsrelevante Fixes erscheinen als Patch-Releases gegen die jüngste Minor-Version.

| Version | Unterstützt |
|---|---|
| 1.x | Ja |
| < 1.0 | Nein |

## Sicherheitslücken melden

Bitte **kein** öffentliches GitHub-Issue für sicherheitsrelevante Findings. Stattdessen per E-Mail an den Maintainer:

- **E-Mail:** `dimitri@justpilot.io`
- **Betreff:** `[Security] billomat-php-api: <kurze Beschreibung>`

Beschreibe:

- den betroffenen Codepfad (Datei + Funktion),
- ein minimales Reproduktionsbeispiel,
- die mögliche Auswirkung,
- ggf. einen Vorschlag zur Behebung.

Antwortzeiten variieren, aber wir bestätigen den Eingang in der Regel innerhalb weniger Werktage und koordinieren danach Fix und Veröffentlichungszeitpunkt mit dir.

## Was nicht in den Scope fällt

- Schwachstellen in der Billomat-API selbst — bitte direkt an [Billomat](https://www.billomat.com/) melden.
- Schwachstellen in transitiven Abhängigkeiten (`symfony/http-client` etc.) — bitte beim jeweiligen Upstream-Projekt melden. Wir aktualisieren die Constraints nach, sobald der Fix verfügbar ist.
