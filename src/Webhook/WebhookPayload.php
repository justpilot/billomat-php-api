<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Webhook;

use JsonException;
use Justpilot\Billomat\Exception\InvalidWebhookException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Model\Article;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\ClientProperty;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\Contact;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\CreditNotePayment;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Model\IncomingComment;
use Justpilot\Billomat\Model\IncomingPayment;
use Justpilot\Billomat\Model\IncomingProperty;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceComment;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferComment;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\Supplier;
use Justpilot\Billomat\Model\SupplierProperty;
use Justpilot\Billomat\Model\SupplierPropertyValue;

use const JSON_THROW_ON_ERROR;

/**
 * Body eines Billomat-Webhooks (Format JSON), aus dem Envelope ausgepackt.
 *
 * Billomat schickt den Datensatz mit demselben Envelope wie die REST-API
 * (`{"invoice-payment": {...}}`), alle Werte als Strings. Der Datensatz ist oft
 * veraltet (bei bezahlten Rechnungen steht meist noch der offene Betrag drin):
 * den Webhook als Trigger behandeln und den aktuellen Stand per `get()` laden.
 *
 * Funktioniert auch mit gespeicherten Requests (Inbox → Worker):
 *
 * ```php
 * $headers = WebhookHeaders::fromArray($row['headers']);
 * $payload = WebhookPayload::fromJson($headers->event, $row['body']);
 *
 * match ($headers->event) {
 *     WebhookEvent::INVOICE_STATUS => $sync->invoice($payload->id()),
 *     WebhookEvent::INVOICE_PAYMENT_CREATE => $sync->invoice($payload->int('invoice_id')),
 *     default => null,
 * };
 * ```
 */
final readonly class WebhookPayload
{
    /**
     * @param array<string, mixed> $data Datensatz ohne Envelope, mit Billomat-Feldnamen
     */
    private function __construct(
        public WebhookEvent $event,
        public array $data,
    ) {
    }

    /**
     * @param array<array-key, mixed> $body dekodierter Body inkl. Envelope
     *
     * @throws InvalidWebhookException wenn der Envelope des Events fehlt
     */
    public static function fromArray(WebhookEvent $event, array $body): self
    {
        $data = $body[$event->envelopeKey()] ?? null;
        if (!\is_array($data) || array_is_list($data) && [] !== $data) {
            throw new InvalidWebhookException(\sprintf('Billomat webhook body for "%s" lacks the "%s" envelope.', $event->value, $event->envelopeKey()));
        }

        /** @var array<string, mixed> $data */
        return new self($event, $data);
    }

    /**
     * @throws InvalidWebhookException bei ungültigem JSON oder fehlendem Envelope
     */
    public static function fromJson(WebhookEvent $event, string $json): self
    {
        try {
            $body = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidWebhookException(\sprintf('Billomat webhook body for "%s" is not valid JSON.', $event->value), $e->getCode(), previous: $e);
        }
        if (!\is_array($body)) {
            throw new InvalidWebhookException(\sprintf('Billomat webhook body for "%s" is not a JSON object.', $event->value));
        }

        return self::fromArray($event, $body);
    }

    /**
     * ID des Datensatzes (`id`), z. B. der Rechnung bei `invoice.status`.
     */
    public function id(): ?int
    {
        return $this->int('id');
    }

    /**
     * Ganzzahliges Feld des Datensatzes, z. B. `invoice_id` einer Zahlung; leer → null.
     */
    public function int(string $field): ?int
    {
        $value = $this->data[$field] ?? null;

        return \is_int($value) || \is_string($value) && ctype_digit($value) ? (int) $value : null;
    }

    /**
     * Datensatz als Model des Events, z. B. `Invoice` bei `invoice.*`, `InvoicePayment` bei `invoice_payment.*`.
     *
     * @return Article|ArticleProperty|ArticlePropertyValue|Client|ClientProperty|ClientPropertyValue|Confirmation|ConfirmationComment|Contact|CreditNote|CreditNoteComment|CreditNotePayment|DeliveryNote|DeliveryNoteComment|Incoming|IncomingComment|IncomingPayment|IncomingProperty|IncomingPropertyValue|Invoice|InvoiceComment|InvoicePayment|Offer|OfferComment|Recurring|Reminder|Supplier|SupplierProperty|SupplierPropertyValue
     *
     * @throws InvalidWebhookException wenn ein Pflichtfeld wie `id` fehlt
     */
    public function model(): object
    {
        try {
            return $this->event->modelClass()::fromArray($this->data);
        } catch (UnexpectedResponseException $e) {
            throw new InvalidWebhookException(\sprintf('Billomat webhook body for "%s" cannot be hydrated: %s', $this->event->value, $e->getMessage()), $e->getCode(), previous: $e);
        }
    }
}
