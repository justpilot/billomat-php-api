<!-- Quelle: https://www.billomat.com/api/eingangsrechnungen/inbox/ -->

# Inbox Documents (Posteingangs-Dokumente)

API-Wrapper für den Billomat-Posteingang unter `/inbox-documents` — also alle PDFs/Bilder, die Billomat per E-Mail, Drag-and-Drop oder API entgegengenommen, aber noch keinem Beleg zugeordnet hat.

## Zugriff

```php
$billomat->inboxDocuments        // hochgeladene, noch nicht verbuchte Belege
```

## Modell

Inbox-Dokumente sind Roh-Anhänge: Billomat speichert ausschließlich Datei-Metadaten (`filename`, `mimetype`, `filesize`) plus den Binärinhalt als Base64-String. Sie haben **keine** Fachdaten — keinen Lieferanten, keinen Betrag, keinen Status.

- **Beziehung zu Incomings**: Inbox-Dokumente sind die typische Vorstufe für einen [Incoming](incomings.md). Im Billomat-Web-UI gibt es einen „in Eingangsrechnung umwandeln“-Workflow; die API stellt dafür **keinen** dedizierten Endpoint bereit. Die Konvertierung läuft daher zweistufig:
  1. Inbox-Dokument lesen (`get($id)` mit Base64-Inhalt).
  2. Neues Incoming anlegen und denselben Base64-String über `IncomingsApi::upload($incomingId, $base64)` daran heften, anschließend `inboxDocuments->delete($id)`.
- **Beziehung zu Suppliers**: keine direkte. Inbox-Dokumente kennen keinen Lieferanten — die Zuordnung passiert erst beim Anlegen des Incomings.

## Endpunkt-Übersicht

### `/inbox-documents`

| Methode | HTTP | Pfad |
|---|---|---|
| `list($filters?)` | GET | `/inbox-documents` |
| `get($id)` | GET | `/inbox-documents/{id}` |
| `create($options)` | POST | `/inbox-documents` |
| `delete($id)` | DELETE | `/inbox-documents/{id}` |

Es gibt **kein** `update()` und **kein** `convertToIncoming()` — die API kennt diese Operationen nicht.

## Methoden

### `list(array $filters = []): list<InboxDocument>`

Listet die Posteingangs-Dokumente. Filter laut Billomat-Doku: `per_page`, `page`, `order_by`. Die zurückgegebenen Modelle enthalten **nicht** den Base64-Inhalt — `base64file` ist in der Listen-Antwort `null`. Für den Binärinhalt muss `get()` aufgerufen werden.

```php
$pending = $billomat->inboxDocuments->list([
    'order_by' => 'created+DESC',
    'per_page' => 50,
]);
```

### `get(int $id): ?InboxDocument`

Liefert `null` bei 404. Im Gegensatz zu `list()` enthält die Detail-Antwort den Base64-codierten Datei-Inhalt unter `base64file`. Mit `getBinary()` lässt er sich direkt als Roh-Bytes ziehen.

```php
$doc = $billomat->inboxDocuments->get(987);
file_put_contents("/tmp/{$doc->filename}", $doc->getBinary());
```

### `create(InboxDocumentCreateOptions $options): InboxDocument`

Lädt eine Datei in den Posteingang hoch.

```php
use Justpilot\Billomat\Api\InboxDocumentCreateOptions;

$opts = new InboxDocumentCreateOptions(
    filename: 'rechnung-mai.pdf',
    mimeType: 'application/pdf',
    base64file: base64_encode((string) file_get_contents('/tmp/rechnung-mai.pdf')),
);

$doc = $billomat->inboxDocuments->create($opts);
```

### `delete(int $id): bool`

Entfernt das Dokument aus dem Posteingang.

## Write-Modell: `InboxDocumentCreateOptions`

Alle drei Felder werden über den Konstruktor gesetzt und sind Pflicht.

| Property | Billomat-Feld | Typ | Notes |
|---|---|---|---|
| `filename` | `filename` | `string` (Pflicht) | inkl. Endung, z. B. `rechnung.pdf` |
| `mimeType` | `mimetype` | `string` (Pflicht) | Property heißt `mimeType` (camelCase), Wire-Feld ist `mimetype` (alles klein) |
| `base64file` | `base64file` | `string` (Pflicht) | Bereits Base64-codierter Inhalt |

## Read-Modell: `InboxDocument`

`final readonly class InboxDocument`.

| Property | Typ |
|---|---|
| `id` | `?int` |
| `created` | `?\DateTimeImmutable` |
| `filename` | `string` |
| `mimeType` | `string` |
| `fileSize` | `int` |
| `base64file` | `?string` |

Plus Helper `getBinary(): string` — dekodiert `base64file` und gibt die Roh-Bytes zurück; leerer String, wenn das Feld leer oder ungültig codiert ist. Auf `InboxDocument` instanzen aus `list()` ist `base64file` üblicherweise `null` — `getBinary()` liefert dann ebenfalls einen leeren String.

## Verwendete Enums

Keine. Die Ressource hat keinen Status-Enum.

## Stolpersteine

- **`base64file` nur über `get()`.** `list()` liefert die Metadaten ohne Binärinhalt, um Antwort-Größen klein zu halten. Wer den PDF-Inhalt verarbeiten will, muss jedes Dokument einzeln per `get($id)` nachladen.
- **Wire-Feld heißt `mimetype`, nicht `mime_type`.** Anders als bei den meisten anderen Ressourcen wird hier kein Snake-Case mit Unterstrich verwendet — Billomat erwartet `mimetype` in einem Wort. Die PHP-Property nennt sich trotzdem `mimeType` (camelCase).
- **Keine API-Konvertierung in ein Incoming.** Die Billomat-API bietet kein `/inbox-documents/{id}/convert` oder ähnlich. Wer das Web-UI-Verhalten nachbauen will, muss selbst `IncomingsApi::create()` plus `IncomingsApi::upload()` aufrufen und das Inbox-Dokument anschließend manuell löschen.
- **Vorab Base64 codieren.** `InboxDocumentCreateOptions` codiert nicht selbst — der String muss bereits Base64 sein. Roh-Bytes durchgeben erzeugt einen Upload, dessen PDF beim Download kaputt ist.
- **Kein Update.** Inhaltliche Korrekturen (z. B. Dateiname tippen) gehen nur über löschen + neu anlegen.
- **`mimeType`-Fallback bei kaputten Responses**: hydriert `InboxDocument::fromArray()` zu `application/octet-stream`, falls Billomat das Feld leerlässt — kein Crash, aber ein Warnsignal beim Logging.

## End-to-End-Beispiel

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

// 1) PDF in den Posteingang hochladen
$pdfPath = '/tmp/lieferantenrechnung.pdf';
$doc = $billomat->inboxDocuments->create(
    new InboxDocumentCreateOptions(
        filename: basename($pdfPath),
        mimeType: 'application/pdf',
        base64file: base64_encode((string) file_get_contents($pdfPath)),
    ),
);

printf("Posteingang: #%d, %d Bytes\n", $doc->id, $doc->fileSize);

// 2) Später: Posteingang abarbeiten — jedes Dokument einzeln laden,
//    in ein Incoming umwandeln und aus dem Posteingang entfernen.
foreach ($billomat->inboxDocuments->list(['order_by' => 'created+ASC']) as $pending) {
    $full = $billomat->inboxDocuments->get($pending->id);
    if (null === $full) {
        continue;
    }

    // 2a) Incoming anlegen (Lieferant und Betrag muss man hier selbst kennen)
    $incomingOpts = new IncomingCreateOptions(supplierId: 4711);
    $incomingOpts->label = $full->filename;
    $incoming = $billomat->incomings->create($incomingOpts);

    // 2b) PDF aus dem Posteingang an den Incoming hängen (Base64 direkt durchreichen)
    $billomat->incomings->upload(
        $incoming->id,
        $full->base64file ?? '',
    );

    // 2c) Dokument aus dem Posteingang entfernen
    $billomat->inboxDocuments->delete($full->id);

    printf("→ #%d in Incoming #%d überführt\n", $full->id, $incoming->id);
}
```
