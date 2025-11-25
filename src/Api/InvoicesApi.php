<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Invoice;

/**
 * API-Wrapper für die Billomat-Invoices-Ressource.
 *
 * - GET  /invoices
 * - GET  /invoices/{id}
 * - POST /invoices
 */
final class InvoicesApi extends AbstractApi
{
    /**
     * Listet Rechnungen mit optionalen Filtern.
     *
     * Entspricht GET /invoices
     *
     * @param array<string, scalar|array|null> $filters
     * @return list<Invoice>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/invoices', $filters);

        $node = $data['invoices']['invoice'] ?? [];

        if ($node === null || $node === []) {
            return [];
        }

        if (is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<Invoice> $models */
        $models = array_map(
            static fn(array $row): Invoice => Invoice::fromArray($row),
            $rows
        );

        return $models;
    }

    /**
     * Holt eine einzelne Rechnung, oder null wenn nicht gefunden.
     *
     * Entspricht GET /invoices/{id}
     */
    public function get(int $id): ?Invoice
    {
        $data = $this->getJsonOrNull("/invoices/{$id}");

        if ($data === null) {
            return null;
        }

        $invoiceData = $data['invoice'] ?? null;

        if (!is_array($invoiceData)) {
            return null;
        }

        return Invoice::fromArray($invoiceData);
    }

    /**
     * Legt eine neue Rechnung an (Draft).
     *
     * Entspricht POST /invoices
     *
     * Status ist bei Erstellung immer DRAFT.
     *
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(InvoiceCreateOptions $options): Invoice
    {
        $payload = [
            'invoice' => $options->toArray(),
        ];

        $data = $this->postJson('/invoices', $payload);

        $created = $data['invoice'] ?? null;

        if (!is_array($created)) {
            throw new \RuntimeException('Unexpected response from Billomat when creating invoice.');
        }

        return Invoice::fromArray($created);
    }
}