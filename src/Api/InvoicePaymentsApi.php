<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\InvoicePayment;

/**
 * API-Wrapper für Rechnungszahlungen (Invoice Payments).
 *
 * Endpoints:
 *  - GET    /invoice-payments
 *  - GET    /invoice-payments/{id}
 *  - POST   /invoice-payments
 *  - DELETE /invoice-payments/{id}
 *
 * Doku: https://www.billomat.com/api/rechnungen/zahlungen/
 */
final class InvoicePaymentsApi extends AbstractApi
{
    /**
     * Listet Zahlungen (optional gefiltert).
     *
     * Unterstützte Filter laut Doku:
     *  - invoice_id
     *  - from (YYYY-MM-DD)
     *  - to (YYYY-MM-DD)
     *  - type (z. B. "CASH", "BANK_TRANSFER", Komma-separiert)
     *  - user_id
     *
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<InvoicePayment>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/invoice-payments', 'invoice-payments', 'invoice-payment', InvoicePayment::fromArray(...), $filters);
    }

    /**
     * Holt eine einzelne Zahlung.
     */
    public function get(int $id): ?InvoicePayment
    {
        $data = $this->getJsonOrNull("/invoice-payments/{$id}");

        if (null === $data) {
            return null;
        }

        $paymentData = $data['invoice-payment'] ?? null;

        if (!\is_array($paymentData)) {
            return null;
        }

        return InvoicePayment::fromArray($paymentData);
    }

    /**
     * Legt eine neue Zahlung an.
     */
    public function create(InvoicePaymentCreateOptions $options): InvoicePayment
    {
        $payload = [
            'invoice-payment' => $options->toArray(),
        ];

        $data = $this->postJson('/invoice-payments', $payload);

        return InvoicePayment::fromArray($this->unwrapEnvelope($data, 'invoice-payment', 'creating invoice payment'));
    }

    /**
     * Löscht eine Zahlung.
     *
     * Laut Doku:
     *  - DELETE /invoice-payments/{id}
     *  - setzt den Status der Rechnung wieder auf OPEN oder OVERDUE
     *
     * @return bool true bei Erfolg
     */
    public function delete(int $id): bool
    {
        $this->deleteVoid("/invoice-payments/{$id}");

        return true;
    }
}
