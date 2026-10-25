<!-- Source: https://www.billomat.com/api/webhooks/ -->

# Webhooks

Billomat can send an HTTP request to a URL of your choice when an event occurs ("webhook"). Exactly one URL can be configured per event type.

> Webhooks have **no REST API endpoint**. They are configured exclusively in the Billomat UI under *Settings → Webhooks* (*Einstellungen → Webhooks*). This SDK can therefore neither create nor list webhooks — but it covers what you actually need: **parsing incoming webhook requests** (`WebhookHeaders`, `WebhookPayload`, `WebhookEvent`, see [below](#receiving-webhooks-with-the-sdk)).

## Configuration in the UI

For each event (see [Event catalog](#event-catalog)) you configure:

- **URL** — target address for the POST request.
- **Format** — `xml` or `json`. Determines the `Content-Type` and body of the request.
- **HTTP Basic Auth (optional)** — user name and password, if the receiver authenticates requests.

## Incoming request

When an event occurs, Billomat sends a POST request to the configured URL.

### Headers

| Header | Meaning |
|---|---|
| `Content-Type` | `application/xml` or `application/json` (depending on the UI setting) |
| `User-Agent` | `Billomat-Webhook` |
| `X-Billomat-Webhook-Id` | ID of the webhook configuration |
| `X-Billomat-Webhook-Request-Id` | Unique, **monotonically increasing** integer per request (e.g. `1792857895`) — suitable for ordering parallel follow-up events |
| `X-Billomat-Webhook-Event` | Event name, e.g. `invoice.create` |

### Body

The body contains the record in the configured format, **with the same envelope as the REST API**: an object with exactly one key, the resource name with a hyphen (`invoice`, `invoice-payment`, `credit-note`, …). Example (JSON, `invoice_payment.create`, shortened):

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

As with the REST API:

- **All values are strings**, including IDs, amounts and flags (`"id": "4711"`, `"amount": "119"`). Empty fields arrive as `""`, not as `null`.
- Nested lists have the same shape as in the REST response, e.g. `"taxes": {"tax": {…}}` with a single object instead of a list when there is only one entry.
- The envelope key follows from the event name: the resource before the dot, with `_` replaced by `-` (`invoice_payment.create` → `invoice-payment`).

That's why the inner record can be hydrated directly with the matching model; `WebhookPayload` does this (see [below](#receiving-webhooks-with-the-sdk)).

### The payload is a trigger, not the current state

The record sent along is often **stale**. Analysis of about 29,000 real webhook requests (three accounts, `invoice.*` and `invoice_payment.*`):

- In **over 99 %** of the `invoice.status` and `invoice.update` events with `status = PAID`, `open_amount` still equals `total_gross` and `paid_amount` is `0`, i.e. the state before the payment.
- `invoice_payment.create` and the related `invoice.status` arrive within the same second (± 1 s) in about 92 % of cases; the order is not guaranteed.

Treat the webhook only as a signal "resource X has changed" and reload the current state via the API (`$billomat->invoices->get($id)`). This also makes processing robust against duplicate deliveries.

## Response behavior

The receiver **must**:

- respond within **10 seconds**,
- acknowledge with an HTTP status code in the `2xx` range.

Anything else counts as a failure. Note:

- **3xx redirects** are not allowed — Billomat doesn't follow them.
- Status **`410 Gone`** signals "URL permanently gone" — Billomat then does **not** retry.
- On any other failure Billomat retries after **1 minute**, then after **1 hour**, then after **6 hours**. If the fourth attempt fails as well, the webhook is deactivated and the account owner is notified by email.
- Follow-up requests of the same event type (e.g. several `invoice.create` in a row) are sent in the order they were triggered. A request that keeps failing blocks the following ones.

## Event catalog

Complete list of events Billomat sends (as of the spec in `docs/spec/billomat.json`):

### Sales

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

### Purchasing

- `incoming.create`, `incoming.update`, `incoming.status`, `incoming.delete`
- `incoming_comment.create`, `incoming_comment.delete`
- `incoming_payment.create`, `incoming_payment.delete`
- `incoming_property.create`, `incoming_property.update`, `incoming_property.delete`
- `incoming_property_value.update`

### Master data

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

## Receiving webhooks with the SDK

The SDK deliberately ships no controller — routing, auth (Basic), idempotency and queueing are the application's job. It does take care of parsing (`src/Webhook/`):

| Class | Responsibility |
|---|---|
| `WebhookEvent` | Backed enum with all 83 events (`WebhookEvent::INVOICE_PAYMENT_CREATE` = `'invoice_payment.create'`); `resource()`, `action()` (`WebhookAction::CREATE` …), `envelopeKey()` (`'invoice-payment'`), `modelClass()` (`InvoicePayment::class`) |
| `WebhookHeaders` | `fromServerRequest()` (PSR-7) or `fromArray()` (`$_SERVER`, Symfony `HeaderBag::all()`, stored headers) → `event`, `requestId` (int), `webhookId` |
| `WebhookPayload` | `fromJson()` / `fromArray()` unwraps the envelope; `data` (raw data), `id()`, `int('invoice_id')`, `model()` hydrates the matching model |
| `InvalidWebhookException` (`Justpilot\Billomat\Exception`) | unknown event, missing header, no JSON, wrong envelope — respond with `4xx` |

The classes also work with stored requests, so the controller only accepts the request and a worker processes it later. Only the JSON format is supported.

```php
<?php

declare(strict_types=1);

use Justpilot\Billomat\Exception\InvalidWebhookException;
use Justpilot\Billomat\Webhook\WebhookEvent;
use Justpilot\Billomat\Webhook\WebhookHeaders;
use Justpilot\Billomat\Webhook\WebhookPayload;

// Controller: accept, store, respond 2xx right away (10 s timeout).
$body = (string) file_get_contents('php://input');
try {
    $headers = WebhookHeaders::fromArray($_SERVER);
    WebhookPayload::fromJson($headers->event, $body); // validate the body before storing it
} catch (InvalidWebhookException) {
    http_response_code(400);
    return;
}

// Idempotency: store the request ID with a unique index, acknowledge duplicates silently.
if (!$inbox->store($headers->requestId, $headers->event->value, $body)) {
    http_response_code(200);
    return;
}
http_response_code(202);

// Worker: the payload is a trigger — reload the current state via the API.
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

`$payload->model()` returns the record as a model (`Invoice`, `InvoicePayment`, …), e.g. for logging. Base decisions on the reloaded state, not on the payload.

## Best practices

- **Process idempotently.** `X-Billomat-Webhook-Request-Id` is unique per request — keep processed IDs in a small table or cache (e.g. with a unique index) and discard them on the second occurrence. Billomat doesn't document whether a retry carries the same ID; if you reload via the API anyway, a duplicate does no harm.
- **Respond 2xx immediately, process asynchronously.** A 10-second timeout is tight. Put the record in a queue and send the HTTP response right away.
- **Always cross-check with the API.** The body is often stale (see [above](#the-payload-is-a-trigger-not-the-current-state)) — fetch the current state with `$billomat->invoices->get($id)` before every business decision.
- **HTTPS + HTTP Basic Auth.** Since Billomat sends no signature, Basic Auth (configurable in the UI) plus TLS is the only protection against forged requests.
- **`410 Gone` to stop.** When you shut down a receiver, respond with `410` — Billomat then stops immediately instead of after the last retry.

## Relation to the SDK

| Concept | SDK counterpart |
|---|---|
| Record from the body | `WebhookPayload::fromJson($event, $body)->model()`; same schema as the REST response |
| Headers | `WebhookHeaders::fromServerRequest($request)` or `fromArray($_SERVER)` |
| `invoice.status`, `invoice.send`, … | correspond to `InvoicesApi::complete()`, `cancel()`, `email()` etc. |
| Reloading the full record | `$billomat->invoices->get($id)`, `$billomat->offers->get($id)`, … |
