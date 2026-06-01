<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\InvoiceTag;
use Justpilot\Billomat\Model\InvoiceTagCloudEntry;
use RuntimeException;

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
        $data = $this->getJson('/invoice-tags', ['invoice_id' => $invoiceId]);

        $root = $data['invoice-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['invoice-tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<InvoiceTag> $tags */
        $tags = array_map(InvoiceTag::fromArray(...), $rows);

        return $tags;
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
        $data = $this->getJson('/invoice-tags');

        $root = $data['invoice-tags'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['tag'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['name'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<InvoiceTagCloudEntry> $tags */
        $tags = array_map(InvoiceTagCloudEntry::fromArray(...), $rows);

        return $tags;
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

        $row = $data['invoice-tag'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating invoice tag.');
        }

        return InvoiceTag::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/invoice-tags/{$id}");

        return true;
    }
}
