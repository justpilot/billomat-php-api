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
     * @param int $invoiceId ID der Rechnung
     * @param array<string, scalar|array|null> $query Zusätzliche Filter (page, per_page, etc.)
     *
     * @return list<InvoiceItem>
     */
    public function listByInvoice(int $invoiceId, array $query = []): array
    {
        $params = array_merge(['invoice_id' => $invoiceId], $query);

        /** @var array<string,mixed> $data */
        $data = $this->getJson('/invoice-items', $params);

        $itemsData = $data['invoice-items']['invoice-item'] ?? [];

        // Billomat liefert bei genau einem Eintrag oft ein einzelnes Array
        if (isset($itemsData['id'])) {
            $itemsData = [$itemsData];
        }

        if (!\is_array($itemsData) || $itemsData === []) {
            return [];
        }

        /** @var list<InvoiceItem> $items */
        $items = array_map(
            static fn(array $row): InvoiceItem => InvoiceItem::fromArray($row),
            $itemsData,
        );

        return $items;
    }

    /**
     * Holt eine einzelne Position über ihre ID.
     *
     * @return InvoiceItem|null null, wenn nicht gefunden (404)
     */
    public function get(int $id): ?InvoiceItem
    {
        $data = $this->getJsonOrNull("/invoice-items/{$id}");

        if ($data === null) {
            return null;
        }

        $itemData = $data['invoice-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new \RuntimeException('Unexpected response from Billomat when fetching invoice item.');
        }

        return InvoiceItem::fromArray($itemData);
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

        /** @var array<string,mixed> $data */
        $data = $this->postJson('/invoice-items', $payload);

        $itemData = $data['invoice-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new \RuntimeException('Unexpected response from Billomat when creating invoice item.');
        }

        return InvoiceItem::fromArray($itemData);
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

        /** @var array<string,mixed> $data */
        $data = $this->putJson("/invoice-items/{$id}", $payload);

        $itemData = $data['invoice-item'] ?? null;

        if (!\is_array($itemData)) {
            throw new \RuntimeException('Unexpected response from Billomat when updating invoice item.');
        }

        return InvoiceItem::fromArray($itemData);
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