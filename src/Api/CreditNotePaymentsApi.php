<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\CreditNotePayment;

/**
 * API-Wrapper für Credit-Note-Payments (Auszahlungen einer Gutschrift).
 *
 * Endpoints:
 *  - GET    /credit-note-payments
 *  - GET    /credit-note-payments/{id}
 *  - POST   /credit-note-payments
 *  - DELETE /credit-note-payments/{id}
 *
 * Doku: https://www.billomat.com/en/api/credit-notes/payments/
 */
final class CreditNotePaymentsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<CreditNotePayment>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/credit-note-payments', 'credit-note-payments', 'credit-note-payment', CreditNotePayment::fromArray(...), $filters);
    }

    public function get(int $id): ?CreditNotePayment
    {
        $data = $this->getJsonOrNull("/credit-note-payments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['credit-note-payment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return CreditNotePayment::fromArray($row);
    }

    public function create(CreditNotePaymentCreateOptions $options): CreditNotePayment
    {
        $payload = ['credit-note-payment' => $options->toArray()];

        $data = $this->postJson('/credit-note-payments', $payload);

        return CreditNotePayment::fromArray($this->unwrapEnvelope($data, 'credit-note-payment', 'creating credit note payment'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-note-payments/{$id}");

        return true;
    }
}
