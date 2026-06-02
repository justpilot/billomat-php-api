<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\InvoiceItem;

/**
 * API-Wrapper für Rechnungspositionen (Invoice Items).
 *
 * Nutzt die Endpunkte:
 *  - GET  /invoice-items?invoice_id={id}
 *  - GET  /invoice-items/{id}
 *  - POST /invoice-items
 *  - PUT  /invoice-items/{id}
 *  - DELETE /invoice-items/{id}
 */
final class InvoiceItemsApi extends AbstractApi
{
    /**
     * Listet alle Positionen einer Rechnung.
     *
     * @param int                              $invoiceId ID der Rechnung
     * @param array<string, scalar|array|null> $query     Zusätzliche Filter (page, per_page, etc.)
     *
     * @return list<InvoiceItem>
     */
    public function listByInvoice(int $invoiceId, array $query = []): array
    {
        $params = array_merge(['invoice_id' => $invoiceId], $query);

        return $this->listResource('/invoice-items', 'invoice-items', 'invoice-item', InvoiceItem::fromArray(...), $params);
    }

    /**
     * Holt eine einzelne Position über ihre ID.
     *
     * @return InvoiceItem|null null, wenn nicht gefunden (404)
     */
    public function get(int $id): ?InvoiceItem
    {
        $data = $this->getJsonOrNull("/invoice-items/{$id}");

        if (null === $data) {
            return null;
        }

        return InvoiceItem::fromArray($this->unwrapEnvelope($data, 'invoice-item', 'fetching invoice item'));
    }

    /**
     * Legt eine neue Position zu einer Rechnung an.
     *
     * Billomat-Endpoint:
     *  POST /invoice-items
     *
     * Erwartet im Payload:
     *  {
     *    "invoice-item": {
     *      "invoice_id": 123,
     *      ...
     *    }
     *  }
     */
    public function create(int $invoiceId, InvoiceItemCreateOptions $options): InvoiceItem
    {
        $body = $options->toArray();
        $body['invoice_id'] = $invoiceId;

        $payload = ['invoice-item' => $body];

        $data = $this->postJson('/invoice-items', $payload);

        return InvoiceItem::fromArray($this->unwrapEnvelope($data, 'invoice-item', 'creating invoice item'));
    }

    /**
     * Aktualisiert eine bestehende Position.
     *
     * Billomat-Endpoint:
     *  PUT /invoice-items/{id}
     */
    public function update(int $id, InvoiceItemCreateOptions $options): InvoiceItem
    {
        $payload = ['invoice-item' => $options->toArray()];

        $data = $this->putJson("/invoice-items/{$id}", $payload);

        return InvoiceItem::fromArray($this->unwrapEnvelope($data, 'invoice-item', 'updating invoice item'));
    }

    /**
     * Löscht eine Position.
     *
     * Billomat-Endpoint:
     *  DELETE /invoice-items/{id}
     *
     * @return bool true, wenn kein HTTP-Fehler geworfen wurde
     */
    public function delete(int $id): bool
    {
        $this->deleteVoid("/invoice-items/{$id}");

        return true;
    }
}
