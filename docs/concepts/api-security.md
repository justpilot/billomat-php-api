<!-- Quelle: https://www.billomat.com/api/grundlagen/api-sicherheit/ -->

# API-Sicherheit

Die Billomat-API ist über `https://{billomatId}.billomat.net/api/` erreichbar — **HTTPS** ist tarifunabhängig verfügbar und sollte für Produktionssysteme zwingend verwendet werden. Ein Klartext-Zugriff über HTTP würde Aufruf-Daten und API-Schlüssel im Netz lesbar machen.

## Was dieses SDK garantiert

- **Base-URI ist HTTPS** — `BillomatConfig::getBaseUri()` baut die URL fest als `https://`. Ein nachträglicher Downgrade auf HTTP ist nicht vorgesehen.
- **API-Schlüssel als Header** — `X-BillomatApiKey` (und optional `X-AppId`/`X-AppSecret`) werden im Request-Header gesendet, nie in der URL. Damit landen Schlüssel **nicht** in Server-Access-Logs, Browser-History oder Referer-Headern (siehe [Authentifizierung](authentication.md)).
- **Symfony-`HttpClient`** als Transport — folgt System-Trust-Store, prüft Zertifikate. TLS-Verifikation lässt sich zwar abschalten, sollte aber nicht.

## Empfehlungen für den Aufrufer

- **API-Schlüssel** wie Passwörter behandeln: nicht im Code, nicht in Git, nicht in Logs. Lokal in `.env.test.local` (gitignored), produktiv in einem Secret-Store.
- Pro Integration einen **eigenen Billomat-Benutzer** anlegen und nur die Rollen vergeben, die wirklich gebraucht werden. Der API-Schlüssel erbt die Rollen seines Benutzers.
- Bei kompromittiertem Schlüssel: im Billomat-UI **revoken** (alten Schlüssel löschen und neuen generieren). Andere Schlüssel bleiben unberührt.
- Webhook-Endpunkte zusätzlich mit **HTTP-Basic-Auth** schützen — Billomat unterstützt das pro Webhook-Konfiguration (siehe [Webhooks](webhooks.md)).
- Bei Apps die App-Header `X-AppId`/`X-AppSecret` mitsenden — auch das ist eine Vertraulichkeits-Schicht (siehe [Errors & Rate-Limits](errors-and-rate-limits.md)).

## Was die API **nicht** absichert

- **Keine IP-Allowlist** — der Schlüssel allein authentisiert, egal von wo. Wer das einschränken will, terminiert TLS vorgelagert und filtert dort.
- **Keine Scope-/Token-Trennung pro Use-Case** — der API-Schlüssel hat immer alle Rechte seines Benutzers. Trennung durch separate Benutzer mit unterschiedlichen Rollen.
- **Keine Rotation** durch die API — Schlüssel werden nur manuell im UI ausgetauscht.
