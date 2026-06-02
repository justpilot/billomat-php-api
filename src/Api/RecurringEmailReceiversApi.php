<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\RecurringEmailReceiver;

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
        return $this->listResource('/recurring-email-receivers', 'recurring-email-receivers', 'recurring-email-receiver', RecurringEmailReceiver::fromArray(...), ['recurring_id' => $recurringId]);
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

        return RecurringEmailReceiver::fromArray($this->unwrapEnvelope($data, 'recurring-email-receiver', 'creating recurring e-mail receiver'));
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/recurring-email-receivers/{$id}");

        return true;
    }
}
