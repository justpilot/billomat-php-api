<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\InvoicePayment;
use RuntimeException;

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
        $data = $this->getJson('/invoice-payments', $filters);

        $paymentsRoot = $data['invoice-payments'] ?? null;
        if (!\is_array($paymentsRoot)) {
            return [];
        }

        $rows = $paymentsRoot['invoice-payment'] ?? [];

        // Billomat liefert bei 1 Element ggf. direkt ein Assoc-Array
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<InvoicePayment> $payments */
        $payments = array_map(
            InvoicePayment::fromArray(...),
            $rows,
        );

        return $payments;
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

        $paymentData = $data['invoice-payment'] ?? null;

        if (!\is_array($paymentData)) {
            throw new RuntimeException('Unexpected response from Billomat when creating invoice payment.');
        }

        return InvoicePayment::fromArray($paymentData);
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
