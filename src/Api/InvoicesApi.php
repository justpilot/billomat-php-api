<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoiceGroupBy;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceGroup;
use Justpilot\Billomat\Model\InvoicePdf;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

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
     *
     * @return list<Invoice>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/invoices', 'invoices', 'invoice', Invoice::fromArray(...), $filters);
    }

    /**
     * Listet Rechnungen aggregiert nach einem oder mehreren Kriterien.
     *
     * Entspricht GET /invoices?group_by=...
     *
     * Mehrere Werte werden in der Reihenfolge der Aggregation als CSV gesendet
     * ("client,year" gruppiert zuerst nach Kunde, dann nach Jahr).
     *
     * @param InvoiceGroupBy|non-empty-list<InvoiceGroupBy> $groupBy
     * @param array<string, scalar|array|null>              $filters optionale zusätzliche Filter (analog zu list())
     *
     * @return list<InvoiceGroup>
     */
    public function listGrouped(InvoiceGroupBy|array $groupBy, array $filters = []): array
    {
        $values = \is_array($groupBy) ? $groupBy : [$groupBy];

        $csv = implode(',', array_map(static fn (InvoiceGroupBy $g): string => $g->value, $values));

        $query = $filters;
        $query['group_by'] = $csv;

        return $this->listResource('/invoices', 'invoice-groups', 'invoice-group', InvoiceGroup::fromArray(...), $query);
    }

    /**
     * Holt eine einzelne Rechnung, oder null wenn nicht gefunden.
     *
     * Entspricht GET /invoices/{id}
     */
    public function get(int $id): ?Invoice
    {
        $data = $this->getJsonOrNull("/invoices/{$id}");

        if (null === $data) {
            return null;
        }

        $invoiceData = $data['invoice'] ?? null;

        if (!\is_array($invoiceData)) {
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

        return Invoice::fromArray($this->unwrapEnvelope($data, 'invoice', 'creating invoice'));
    }

    /**
     * Bearbeitet eine Rechnung im Status DRAFT.
     *
     * Entspricht PUT /invoices/{id}
     *
     * Hinweis:
     * - Nur DRAFT ist editierbar.
     * - Positionen/Kommentare nicht hier, sondern über die jeweilige Ressource.
     */
    public function update(int $id, InvoiceUpdateOptions $options): Invoice
    {
        $payload = [
            'invoice' => $options->toArray(),
        ];

        $data = $this->putJson("/invoices/{$id}", $payload);

        return Invoice::fromArray($this->unwrapEnvelope($data, 'invoice', 'updating invoice'));
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
     * @param int      $id         ID der Rechnung
     * @param int|null $templateId Optionale ID der Vorlage für die PDF-Erzeugung
     */
    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            // laut Doku: optionaler Parameter template_id
            // Wir senden ihn im invoice-Block
            $body['template_id'] = $templateId;
        }

        $payload = ['invoice' => $body];

        $this->putVoid("/invoices/{$id}/complete", $payload);

        return true;
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

    /**
     * Markiert eine Rechnung als storniert.
     *
     * Entspricht PUT /invoices/{id}/cancel
     *
     * @return bool true bei Erfolg
     */
    public function cancel(int $id): bool
    {
        $this->putVoid("/invoices/{$id}/cancel");

        return true;
    }

    /**
     * Hebt die Stornierung einer Rechnung wieder auf.
     *
     * Entspricht PUT /invoices/{id}/uncancel
     *
     * @return bool true bei Erfolg
     */
    public function uncancel(int $id): bool
    {
        $this->putVoid("/invoices/{$id}/uncancel");

        return true;
    }

    /**
     * Versendet eine Rechnung per E-Mail.
     *
     * Entspricht POST /invoices/{id}/email.
     *
     * Wenn keine Optionen angegeben werden, verwendet Billomat alle Defaults
     * (Absender aus den Settings, Empfänger aus den Kunden-Stammdaten,
     * Default-Subject/Body, PDF als Anhang).
     */
    public function email(int $id, ?InvoiceEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/invoices/{$id}/email", $payload);

        return true;
    }

    /**
     * Versendet eine Rechnung postalisch über den Pixelletter-Service.
     *
     * Entspricht POST /invoices/{id}/mail.
     *
     * ⚠️ Kostenpflichtige Aktion.
     */
    public function mail(int $id, ?InvoiceMailOptions $options = null): bool
    {
        $payload = ['mail' => $options?->toArray() ?? []];

        $this->postJson("/invoices/{$id}/mail", $payload);

        return true;
    }

    /**
     * Lädt eine unterschriebene PDF-Version der Rechnung hoch.
     *
     * Entspricht PUT /invoices/{id}/upload-signature.
     *
     * @param string $base64Pdf base64-codierter PDF-Inhalt der unterschriebenen Rechnung
     */
    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/invoices/{$id}/upload-signature", $payload);

        return true;
    }

    /**
     * Übergibt eine Rechnung an das Inkasso-Verfahren.
     *
     * Entspricht PUT /invoices/{id}/encash.
     *
     * Voraussetzungen laut Billomat: Rechnung muss OPEN oder OVERDUE sein.
     */
    public function encash(int $id): bool
    {
        $this->putVoid("/invoices/{$id}/encash");

        return true;
    }

    /**
     * Ruft das PDF einer Rechnung ab.
     *
     * Entspricht GET /invoices/{id}/pdf
     *
     * - Optionaler Typ:
     *   - InvoicePdfType::SIGNED → type=signed
     *   - InvoicePdfType::PRINT  → type=print
     *
     * - Raw-PDF-Modus:
     *   - $rawPdf = true → format=pdf, Rückgabe ist der binäre PDF-String
     *   - $rawPdf = false (Default) → JSON-Response mit base64file, Rückgabe ist InvoicePdf-Model
     *
     * @return InvoicePdf|string InvoicePdf im JSON-Modus oder binärer PDF-Inhalt im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): InvoicePdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            // Enum → API-String (signed / print)
            $query['type'] = $type->value;
        }

        // Raw-PDF-Modus: format=pdf → direkt application/pdf
        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/invoices/{$id}/pdf", $query);

            try {
                // Binärer PDF-Content
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        // Standard-Modus: JSON → { "pdf": { ... } }
        $data = $this->getJson("/invoices/{$id}/pdf", $query);

        return InvoicePdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching invoice PDF'));
    }
}
