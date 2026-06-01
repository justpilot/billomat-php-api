<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Aggregat-Eintrag aus GET /invoices?group_by=...
 *
 * Welche der optionalen Identifier-Felder gesetzt sind, hängt vom group_by-Parameter ab:
 *  - group_by=client → clientId
 *  - group_by=status → status
 *  - group_by=day/week/month/year → eines der Zeit-Felder
 *
 * `invoiceParams` enthält das Filterset, das Billomat zurückgibt, damit man den Aggregat-
 * Eintrag in einen Folge-Request an `list()` weiterreichen kann (Drill-down).
 *
 * Doku: https://www.billomat.com/api/rechnungen/ (Abschnitt "Rechnungen aggregiert auflisten")
 */
final readonly class InvoiceGroup
{
    /**
     * @param array<string, scalar> $invoiceParams Filter zum Drill-down (z. B. ['client_id' => 476]).
     */
    public function __construct(
        public ?float $totalGross,
        public ?float $totalNet,
        public ?int $clientId = null,
        public ?string $status = null,
        public ?string $day = null,
        public ?string $week = null,
        public ?string $month = null,
        public ?string $year = null,
        public array $invoiceParams = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $params = [];
        if (isset($data['invoice-params']) && \is_array($data['invoice-params'])) {
            foreach ($data['invoice-params'] as $key => $value) {
                if (\is_scalar($value)) {
                    $params[(string) $key] = $value;
                }
            }
        }

        return new self(
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            clientId: isset($data['client_id']) && '' !== $data['client_id']
                ? (int) $data['client_id']
                : null,
            status: isset($data['status']) && '' !== $data['status']
                ? (string) $data['status']
                : null,
            day: isset($data['day']) && '' !== $data['day'] ? (string) $data['day'] : null,
            week: isset($data['week']) && '' !== $data['week'] ? (string) $data['week'] : null,
            month: isset($data['month']) && '' !== $data['month'] ? (string) $data['month'] : null,
            year: isset($data['year']) && '' !== $data['year'] ? (string) $data['year'] : null,
            invoiceParams: $params,
        );
    }
}
