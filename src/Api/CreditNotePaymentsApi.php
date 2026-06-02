<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\CreditNotePayment;
use RuntimeException;

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
        $data = $this->getJson('/credit-note-payments', $filters);

        $root = $data['credit-note-payments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['credit-note-payment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<CreditNotePayment> $payments */
        $payments = array_map(
            CreditNotePayment::fromArray(...),
            $rows,
        );

        return $payments;
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

        $row = $data['credit-note-payment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating credit note payment.');
        }

        return CreditNotePayment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/credit-note-payments/{$id}");

        return true;
    }
}
