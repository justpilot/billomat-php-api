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

    /**
     * Schließt eine Rechnung im Entwurfsstatus (DRAFT) ab.
     *
     * Entspricht PUT /invoices/{id}/complete
     *
     * - Status wird von DRAFT auf OPEN / OVERDUE / PAID gesetzt (laut Billomat-Logik)
     * - Es wird ein PDF erzeugt
     * - Die Rechnungsnummer (invoice_number) wird vergeben
     *
     * @param int $id ID der Rechnung
     * @param int|null $templateId Optionale ID der Vorlage für die PDF-Erzeugung
     */
    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if ($templateId !== null) {
            // laut Doku: optionaler Parameter template_id
            // Wir senden ihn im invoice-Block
            $body['template_id'] = $templateId;
        }

        $payload = ['invoice' => $body];

        $response = $this->putEmptyResponse("/invoices/{$id}/complete", $payload);
        return $response->getStatusCode() === 200;
    }

    /**
     * Löscht eine Rechnung.
     *
     * Nur Rechnungen im Status DRAFT können gelöscht werden.
     *
     * @throws ValidationException wenn die Rechnung nicht DRAFT ist
     */
    public function delete(int $id): bool
    {
        $this->deleteVoid("/invoices/{$id}");
        return true;
    }
}