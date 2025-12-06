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

    /** Rechnungsdatum. */
    public ?\DateTimeImmutable $date;

    /** Fälligkeitsdatum. */
    public ?\DateTimeImmutable $dueDate;

    /** Währungscode, z. B. "EUR". */
    public ?string $currencyCode;

    /** Status, z. B. DRAFT, OPEN, PAID. */
    public ?InvoiceStatus $status;

    /** Bruttosumme der Rechnung (falls von API bereitgestellt). */
    public ?float $totalGross;

    /** Nettosumme der Rechnung (falls von API bereitgestellt). */
    public ?float $totalNet;

    /**
     * Rechnungspositionen, falls im API-Response enthalten.
     *
     * @var list<InvoiceItem>
     */
    public array $items;

    /**
     * @param list<InvoiceItem> $items
     */
    public function __construct(
        ?int                $id,
        int                 $clientId,
        ?int                $contactId = null,
        ?string             $invoiceNumber = null,
        ?\DateTimeImmutable $date = null,
        ?\DateTimeImmutable $dueDate = null,
        ?string             $currencyCode = null,
        ?InvoiceStatus      $status = null,
        ?float              $totalGross = null,
        ?float              $totalNet = null,
        array               $items = [],
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
        $this->items = $items;
    }

    /**
     * Hydriert eine Invoice aus einem Billomat-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $date = null;
        if (!empty($data['date'])) {
            try {
                $date = new \DateTimeImmutable((string)$data['date']);
            } catch (\Throwable) {
                $date = null;
            }
        }

        $dueDate = null;
        if (!empty($data['due_date'])) {
            try {
                $dueDate = new \DateTimeImmutable((string)$data['due_date']);
            } catch (\Throwable) {
                $dueDate = null;
            }
        }

        // Invoice-Items, falls vorhanden (z. B. bei bestimmten GET-Requests)
        $items = [];
        if (isset($data['invoice-items']['invoice-item'])) {
            $rawItems = $data['invoice-items']['invoice-item'];

            // Billomat liefert bei 1 Item teilweise ein einzelnes Array statt Liste
            if (isset($rawItems['id'])) {
                $rawItems = [$rawItems];
            }

            if (\is_array($rawItems)) {
                $items = array_map(
                    static fn(array $row): InvoiceItem => InvoiceItem::fromArray($row),
                    $rawItems,
                );
            }
        }

        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            clientId: (int)($data['client_id'] ?? 0),
            contactId: isset($data['contact_id']) ? (int)$data['contact_id'] : null,
            invoiceNumber: $data['invoice_number'] ?? null,
            date: $date,
            dueDate: $dueDate,
            currencyCode: $data['currency_code'] ?? null,
            status: InvoiceStatus::fromApi($data['status'] ?? null),
            totalGross: isset($data['total_gross']) ? (float)$data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float)$data['total_net'] : null,
            items: $items,
        );
    }

    /**
     * Exportiert die Rechnung als Array mit Billomat-Feldnamen.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'invoice_number' => $this->invoiceNumber,
            'date' => $this->date?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'currency_code' => $this->currencyCode,
            'status' => $this->status?->value,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
        ];

        if ($this->items !== []) {
            $data['invoice-items'] = [
                'invoice-item' => array_map(
                    static fn(InvoiceItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }
}