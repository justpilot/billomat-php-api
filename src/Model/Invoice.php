<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Model\Enum\InvoiceStatus;

/**
 * Repräsentiert eine Rechnung aus der Billomat-API.
 *
 * Status ist bei Erstellung immer DRAFT. Die endgültige Rechnungsnummer
 * (invoice_number) wird erst beim Abschluss der Rechnung gesetzt.
 */
final readonly class Invoice
{
    /** Interne Billomat-ID der Rechnung. */
    public ?int $id;

    /** ID des Kunden. */
    public int $clientId;

    /** ID des Kontakts (optional). */
    public ?int $contactId;

    /** Rechnungsnummer (kann bei DRAFT leer sein). */
    public ?string $invoiceNumber;

    /** Rechnungsdatum (YYYY-MM-DD). */
    public ?string $date;

    /** Fälligkeitsdatum (YYYY-MM-DD). */
    public ?string $dueDate;

    /** Währungscode, z. B. "EUR". */
    public ?string $currencyCode;

    /** Status, z. B. "DRAFT", "OPEN", "PAID". */
    public ?InvoiceStatus $status;

    /** Bruttosumme der Rechnung (falls von API bereitgestellt). */
    public ?float $totalGross;

    /** Nettosumme der Rechnung (falls von API bereitgestellt). */
    public ?float $totalNet;

    public function __construct(
        ?int           $id,
        int            $clientId,
        ?int           $contactId = null,
        ?string        $invoiceNumber = null,
        ?string        $date = null,
        ?string        $dueDate = null,
        ?string        $currencyCode = null,
        ?InvoiceStatus $status = null,
        ?float         $totalGross = null,
        ?float         $totalNet = null,
    )
    {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->contactId = $contactId;
        $this->invoiceNumber = $invoiceNumber;
        $this->date = $date;
        $this->dueDate = $dueDate;
        $this->currencyCode = $currencyCode;
        $this->status = $status;
        $this->totalGross = $totalGross;
        $this->totalNet = $totalNet;
    }

    /**
     * Hydriert eine Invoice aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            clientId: (int)($data['client_id'] ?? 0),
            contactId: isset($data['contact_id']) ? (int)$data['contact_id'] : null,
            invoiceNumber: $data['invoice_number'] ?? null,
            date: $data['date'] ?? null,
            dueDate: $data['due_date'] ?? null,
            currencyCode: $data['currency_code'] ?? null,
            status: InvoiceStatus::fromApi($data['status'] ?? null),
            totalGross: isset($data['total_gross']) ? (float)$data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float)$data['total_net'] : null,
        );
    }

    /**
     * Exportiert die Rechnung als Array mit Billomat-Feldnamen.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'invoice_number' => $this->invoiceNumber,
            'date' => $this->date,
            'due_date' => $this->dueDate,
            'currency_code' => $this->currencyCode,
            'status' => $this->status,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
        ];
    }
}