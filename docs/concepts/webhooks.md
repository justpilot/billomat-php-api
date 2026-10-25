<!-- Quelle: https://www.billomat.com/api/webhooks/ -->

# Webhooks

Billomat kann auf Ereignisse hin einen HTTP-Aufruf an eine selbst gewählte URL absetzen ("Webhook"). Für jeden Ereignistyp lässt sich genau eine URL hinterlegen.

> Webhooks haben **keinen REST-API-Endpunkt**. Sie werden ausschließlich im Billomat-UI unter *Einstellungen → Webhooks* konfiguriert. Dieses SDK kann also weder Webhooks anlegen noch auflisten — wohl aber das, was wirklich gebraucht wird: **eingehende Webhook-Requests auswerten** (`WebhookHeaders`, `WebhookPayload`, `WebhookEvent`, siehe [unten](#webhooks-empfangen-mit-dem-sdk)).

## Konfiguration im UI

Pro Ereignis (siehe [Event-Katalog](#event-katalog)) wird hinterlegt:

- **URL** — Zieladresse für den POST-Request.
- **Format** — `xml` oder `json`. Bestimmt `Content-Type` und Body des Requests.
- **HTTP-Basic-Auth (optional)** — Nutzername und Passwort, falls der Empfänger authentifiziert.

## Eingehender Request

Tritt ein Ereignis ein, schickt Billomat einen POST-Request an die hinterlegte URL.

### Header

| Header | Bedeutung |
|---|---|
| `Content-Type` | `application/xml` oder `application/json` (je nach UI-Einstellung) |
| `User-Agent` | `Billomat-Webhook` |
| `X-Billomat-Webhook-Id` | ID der hinterlegten Webhook-Konfiguration |
| `X-Billomat-Webhook-Request-Id` | Eindeutige, **monoton steigende** Ganzzahl pro Request (z. B. `1792857895`) — geeignet zum Sortieren paralleler Folge-Ereignisse |
| `X-Billomat-Webhook-Event` | Eventname, z.B. `invoice.create` |

### Body

Der Body enthält den Datensatz im konfigurierten Format, **mit demselben Envelope wie die REST-API**: ein Objekt mit genau einem Schlüssel, dem Ressourcennamen mit Bindestrich (`invoice`, `invoice-payment`, `credit-note`, …). Beispiel (JSON, `invoice_payment.create`, gekürzt):

```json
{
  "invoice-payment": {
    "id": "4711",
    "created": "2026-10-20T09:14:02+02:00",
    "invoice_id": "1234",
    "user_id": "",
    "date": "2026-10-20",
    "amount": "119",
    "comment": "",
    "transaction_purpose": "RE-2026-0042",
    "currency_code": "EUR",
    "quote": "1",
    "type": "BANK_TRANSFER"
  }
}
```

Wie bei der REST-API gilt:

- **Alle Werte sind Strings**, auch IDs, Beträge und Flags (`"id": "4711"`, `"amount": "119"`). Leere Felder kommen als `""`, nicht als `null`.
- Verschachtelte Listen haben dieselbe Form wie in der REST-Antwort, z. B. `"taxes": {"tax": {…}}` mit einem einzelnen Objekt statt einer Liste, wenn es nur einen Eintrag gibt.
- Der Envelope-Schlüssel folgt aus dem Eventnamen: Ressource vor dem Punkt, `_` durch `-` ersetzt (`invoice_payment.create` → `invoice-payment`).

Deshalb lässt sich der innere Datensatz direkt mit dem passenden Model hydrieren; `WebhookPayload` erledigt das (siehe [unten](#webhooks-empfangen-mit-dem-sdk)).

### Der Payload ist ein Trigger, kein aktueller Stand

Der mitgeschickte Datensatz ist oft **veraltet**. Auswertung von rund 29.000 echten Webhook-Requests (drei Accounts, `invoice.*` und `invoice_payment.*`):

- In **über 99 %** der `invoice.status`- und `invoice.update`-Events mit `status = PAID` steht noch `open_amount` = `total_gross` und `paid_amount` = `0`, also der Stand vor der Zahlung.
- `invoice_payment.create` und das zugehörige `invoice.status` kommen in rund 92 % der Fälle in derselben Sekunde (± 1 s) an; die Reihenfolge ist nicht garantiert.

Den Webhook daher nur als Signal „Ressource X hat sich geändert“ behandeln und den aktuellen Stand per API nachladen (`$billomat->invoices->get($id)`). Das macht die Verarbeitung zugleich unempfindlich gegen doppelt zugestellte Requests.

## Antwortverhalten

Der Empfänger **muss**:

- innerhalb von **10 Sekunden** antworten,
- mit einem HTTP-Statuscode aus `2xx` quittieren.

Alles andere zählt als Fehler. Wichtig:

- **3xx-Weiterleitungen** sind nicht erlaubt — Billomat folgt ihnen nicht.
- Status **`410 Gone`** signalisiert "URL endgültig weg" — Billomat retryt dann **nicht**.
- Bei jedem anderen Fehler retryt Billomat nach **1 Minute**, danach nach **1 Stunde**, danach nach **6 Stunden**. Schlägt auch der vierte Versuch fehl, wird der Webhook deaktiviert und der Account-Inhaber per E-Mail informiert.
- Folge-Requests desselben Ereignis-Typs (z.B. mehrere `invoice.create` hintereinander) werden in der Reihenfolge der Auslösung gefeuert. Bleibt ein Request dauerhaft hängen, blockiert er die folgenden.

## Event-Katalog

Vollständige Liste der von Billomat gesendeten Events (Stand der Spec: `docs/spec/billomat.json`):

### Verkauf

- `invoice.create`, `invoice.update`, `invoice.status`, `invoice.send`, `invoice.delete`
- `invoice_comment.create`, `invoice_comment.delete`
- `invoice_payment.create`, `invoice_payment.delete`
- `offer.create`, `offer.update`, `offer.status`, `offer.send`, `offer.delete`
- `offer_comment.create`, `offer_comment.delete`
- `confirmation.create`, `confirmation.update`, `confirmation.status`, `confirmation.send`, `confirmation.delete`
- `confirmation_comment.create`, `confirmation_comment.delete`
- `credit_note.create`, `credit_note.update`, `credit_note.status`, `credit_note.send`, `credit_note.delete`
- `credit_note_comment.create`, `credit_note_comment.delete`
- `credit_note_payment.create`, `credit_note_payment.delete`
- `delivery_note.create`, `delivery_note.update`, `delivery_note.status`, `delivery_note.send`, `delivery_note.delete`
- `delivery_note_comment.create`, `delivery_note_comment.delete`
- `reminder.create`, `reminder.update`, `reminder.status`, `reminder.send`, `reminder.delete`
- `recurring.create`, `recurring.update`, `recurring.delete`

### Einkauf

- `incoming.create`, `incoming.update`, `incoming.status`, `incoming.delete`
- `incoming_comment.create`, `incoming_comment.delete`
- `incoming_payment.create`, `incoming_payment.delete`
- `incoming_property.create`, `incoming_property.update`, `incoming_property.delete`
- `incoming_property_value.update`

### Stammdaten

- `article.create`, `article.update`, `article.delete`
- `article_property.create`, `article_property.update`, `article_property.delete`
- `article_property_value.update`
- `client.create`, `client.update`, `client.delete`
- `client_property.create`, `client_property.update`, `client_property.delete`
- `client_property_value.update`
- `contact.create`, `contact.update`, `contact.delete`
- `supplier.create`, `supplier.update`, `supplier.delete`
- `supplier_property.create`, `supplier_property.update`, `supplier_property.delete`
- `supplier_property_value.update`

## Webhooks empfangen mit dem SDK

Das SDK liefert bewusst keinen Controller mit — Routing, Auth (Basic), Idempotenz und Queue sind Aufgabe der Anwendung. Es nimmt aber das Auswerten ab (`src/Webhook/`):

| Klasse | Aufgabe |
|---|---|
| `WebhookEvent` | Backed Enum mit allen 83 Events (`WebhookEvent::INVOICE_PAYMENT_CREATE` = `'invoice_payment.create'`); `resource()`, `action()` (`WebhookAction::CREATE` …), `envelopeKey()` (`'invoice-payment'`), `modelClass()` (`InvoicePayment::class`) |
| `WebhookHeaders` | `fromServerRequest()` (PSR-7) bzw. `fromArray()` (`$_SERVER`, Symfony-`HeaderBag::all()`, gespeicherte Header) → `event`, `requestId` (int), `webhookId` |
| `WebhookPayload` | `fromJson()` / `fromArray()` packt den Envelope aus; `data` (Rohdaten), `id()`, `int('invoice_id')`, `model()` hydriert das passende Model |
| `InvalidWebhookException` | unbekanntes Event, fehlender Header, kein JSON, falscher Envelope — mit `4xx` antworten |

Die Klassen arbeiten auch mit gespeicherten Requests, damit der Controller nur annimmt und ein Worker später verarbeitet. Nur das JSON-Format wird unterstützt.

```php
<?php

declare(strict_types=1);

use Justpilot\Billomat\Exception\InvalidWebhookException;
use Justpilot\Billomat\Webhook\WebhookEvent;
use Justpilot\Billomat\Webhook\WebhookHeaders;
use Justpilot\Billomat\Webhook\WebhookPayload;

// Controller: annehmen, speichern, sofort 2xx (10 s Timeout).
$body = (string) file_get_contents('php://input');
try {
    $headers = WebhookHeaders::fromArray($_SERVER);
    WebhookPayload::fromJson($headers->event, $body); // Body prüfen, bevor er gespeichert wird
} catch (InvalidWebhookException) {
    http_response_code(400);
    return;
}

// Idempotenz: Request-ID mit Unique-Index speichern, Duplikate still quittieren.
if (!$inbox->store($headers->requestId, $headers->event->value, $body)) {
    http_response_code(200);
    return;
}
http_response_code(202);

// Worker: der Payload ist ein Trigger — aktuellen Stand per API nachladen.
$payload = WebhookPayload::fromJson(WebhookEvent::from($row['event']), $row['body']);

match ($payload->event) {
    WebhookEvent::INVOICE_CREATE, WebhookEvent::INVOICE_UPDATE, WebhookEvent::INVOICE_STATUS
        => $sync->invoice($billomat->invoices->get((int) $payload->id())),
    WebhookEvent::INVOICE_PAYMENT_CREATE, WebhookEvent::INVOICE_PAYMENT_DELETE
        => $sync->invoice($billomat->invoices->get((int) $payload->int('invoice_id'))),
    WebhookEvent::INVOICE_DELETE => $sync->deleteInvoice((int) $payload->id()),
    default => null,
};
```

`$payload->model()` liefert den Datensatz als Model (`Invoice`, `InvoicePayment`, …), etwa fürs Logging. Für Entscheidungen den nachgeladenen Stand verwenden, nicht den Payload.

## Best Practices

- **Idempotent verarbeiten.** `X-Billomat-Webhook-Request-Id` ist eindeutig pro Request — verarbeitete IDs in einer kleinen Tabelle / einem Cache vorhalten (z. B. mit Unique-Index) und beim zweiten Erscheinen verwerfen. Ob ein Retry dieselbe ID trägt, dokumentiert Billomat nicht; wer ohnehin per API nachlädt, verarbeitet ein Duplikat ohne Schaden.
- **Sofort 2xx zurück, asynchron verarbeiten.** 10 Sekunden Timeout sind eng. Den Datensatz in eine Queue legen und die HTTP-Antwort sofort senden.
- **Immer gegen die API gegenchecken.** Der Body ist oft veraltet (siehe [oben](#der-payload-ist-ein-trigger-kein-aktueller-stand)) — vor jeder fachlichen Entscheidung mit `$billomat->invoices->get($id)` den aktuellen Stand holen.
- **HTTPS + HTTP-Basic-Auth.** Da Billomat keine Signatur mitschickt, ist Basic-Auth (im UI eintragbar) plus TLS die einzige Absicherung gegen gefälschte Aufrufe.
- **`410 Gone` zum Beenden.** Wird ein Empfänger abgeschaltet, mit `410` antworten — Billomat hört dann sofort auf und stoppt nicht erst nach dem letzten Retry.

## Bezug zum SDK

| Konzept | SDK-Bezug |
|---|---|
| Datensatz aus dem Body | `WebhookPayload::fromJson($event, $body)->model()`; gleiches Schema wie die REST-Antwort |
| Header | `WebhookHeaders::fromServerRequest($request)` bzw. `fromArray($_SERVER)` |
| `invoice.status`, `invoice.send`, … | korrespondieren zu `InvoicesApi::complete()`, `cancel()`, `email()` etc. |
| Vollständigen Datensatz nachladen | `$billomat->invoices->get($id)`, `$billomat->offers->get($id)`, … |
