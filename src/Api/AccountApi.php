<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Account;

/**
 * API-Wrapper für die eigenen Account-Informationen.
 *
 * Der Account-Datensatz wird von Billomat über den Kunden-Endpunkt
 * mit der speziellen ID `myself` ausgeliefert (GET /clients/myself).
 * Im Gegensatz zu gewöhnlichen Clients enthält die Antwort zusätzlich
 * den aktuellen Tarif (`plan`) und das Kontingent (`quotas`).
 *
 * Doku: https://www.billomat.com/api/account/
 */
final class AccountApi extends AbstractApi
{
    /**
     * Liefert die eigenen Account-Informationen.
     *
     * Entspricht GET /clients/myself.
     */
    public function get(): Account
    {
        $data = $this->getJson('/clients/myself');

        return Account::fromArray($this->unwrapEnvelope($data, 'client', 'fetching own account via /clients/myself'));
    }
}
