<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\InvoiceTag;
use Justpilot\Billomat\Model\InvoiceTagCloudEntry;

/**
 * API-Wrapper für Rechnungs-Schlagworte (Invoice Tags).
 *
 * Endpoints:
 *  - GET    /invoice-tags?invoice_id={id} (Tags einer Rechnung)
 *  - GET    /invoice-tags                  (Tag-Cloud, aggregiert)
 *  - GET    /invoice-tags/{id}
 *  - POST   /invoice-tags
 *  - DELETE /invoice-tags/{id}
 *
 * Doku: https://www.billomat.com/api/rechnungen/schlagworte/
 */
final class InvoiceTagsApi extends AbstractApi
{
    /**
     * Listet die Schlagworte einer einzelnen Rechnung.
     *
     * Antwort-Pfad: invoice-tags.invoice-tag → Liste/Single → InvoiceTag.
     *
     * @return list<InvoiceTag>
     */
    public function listByInvoice(int $invoiceId): array
    {
        return $this->listResource('/invoice-tags', 'invoice-tags', 'invoice-tag', InvoiceTag::fromArray(...), ['invoice_id' => $invoiceId]);
    }

    /**
     * Tag-Cloud: alle Tags aggregiert mit Häufigkeit.
     *
     * Antwort-Pfad: invoice-tags.tag → Liste/Single → InvoiceTagCloudEntry.
     *
     * @return list<InvoiceTagCloudEntry>
     */
    public function cloud(): array
    {
        return $this->listResource('/invoice-tags', 'invoice-tags', 'tag', InvoiceTagCloudEntry::fromArray(...));
    }

    public function get(int $id): ?InvoiceTag
    {
        $data = $this->getJsonOrNull("/invoice-tags/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['invoice-tag'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return InvoiceTag::fromArray($row);
    }

    public function create(InvoiceTagCreateOptions $options): InvoiceTag
    {
        $payload = ['invoice-tag' => $options->toArray()];

        $data = $this->postJson('/invoice-tags', $payload);

        return InvoiceTag::fromArray($this->unwrapEnvelope($data, 'invoice-tag', 'creating invoice tag'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/invoice-tags/{$id}");

        return true;
    }
}
