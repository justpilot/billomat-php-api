<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\RecurringEmailReceiver;
use RuntimeException;

/**
 * API-Wrapper für E-Mail-Empfänger einer Abo-Rechnung.
 *
 * Endpoints:
 *  - GET    /recurring-email-receivers?recurring_id={id}
 *  - GET    /recurring-email-receivers/{id}
 *  - POST   /recurring-email-receivers
 *  - DELETE /recurring-email-receivers/{id}
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/email-empfaenger/
 */
final class RecurringEmailReceiversApi extends AbstractApi
{
    /**
     * @return list<RecurringEmailReceiver>
     */
    public function listByRecurring(int $recurringId): array
    {
        $data = $this->getJson('/recurring-email-receivers', ['recurring_id' => $recurringId]);

        $root = $data['recurring-email-receivers'] ?? null;
        if (!\is_array($root)) {
            return [];
        }

        $rows = $root['recurring-email-receiver'] ?? [];
        if ([] === $rows || null === $rows) {
            return [];
        }

        if (isset($rows['id'])) {
            $rows = [$rows];
        }

        if (!\is_array($rows)) {
            return [];
        }

        /** @var list<RecurringEmailReceiver> $receivers */
        $receivers = array_map(RecurringEmailReceiver::fromArray(...), $rows);

        return $receivers;
    }

    public function get(int $id): ?RecurringEmailReceiver
    {
        $data = $this->getJsonOrNull("/recurring-email-receivers/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['recurring-email-receiver'] ?? null;
        if (!\is_array($row)) {
            return null;
        }

        return RecurringEmailReceiver::fromArray($row);
    }

    public function create(RecurringEmailReceiverCreateOptions $options): RecurringEmailReceiver
    {
        $payload = ['recurring-email-receiver' => $options->toArray()];

        $data = $this->postJson('/recurring-email-receivers', $payload);

        $row = $data['recurring-email-receiver'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when creating recurring e-mail receiver.');
        }

        return RecurringEmailReceiver::fromArray($row);
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurring-email-receivers/{$id}");

        return true;
    }
}
