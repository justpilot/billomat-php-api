<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingPayment;
use RuntimeException;

/**
 * API-Wrapper für Incoming-Payments.
 *
 * Doku: https://www.billomat.com/en/api/incomings/payments/
 */
final class IncomingPaymentsApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<IncomingPayment>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/incoming-payments', $filters);

        $root = $data['incoming-payments'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['incoming-payment'] ?? [];

        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<IncomingPayment> $payments */
        $payments = array_map(
            IncomingPayment::fromArray(...),
            $rows,
        );

        return $payments;
    }

    public function get(int $id): ?IncomingPayment
    {
        $data = $this->getJsonOrNull("/incoming-payments/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['incoming-payment'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return IncomingPayment::fromArray($row);
    }

    public function create(IncomingPaymentCreateOptions $options): IncomingPayment
    {
        $payload = ['incoming-payment' => $options->toArray()];

        $data = $this->postJson('/incoming-payments', $payload);

        $row = $data['incoming-payment'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating incoming payment.');
        }

        return IncomingPayment::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-payments/{$id}");

        return true;
    }
}
