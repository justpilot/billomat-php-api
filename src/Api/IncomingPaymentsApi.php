<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\IncomingPayment;

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
        return $this->listResource('/incoming-payments', 'incoming-payments', 'incoming-payment', IncomingPayment::fromArray(...), $filters);
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

        return IncomingPayment::fromArray($this->unwrapEnvelope($data, 'incoming-payment', 'creating incoming payment'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/incoming-payments/{$id}");

        return true;
    }
}
