<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\RecurringEmailReceiver;
use NoDiscard;

/**
 * E-Mail-Empfänger einer Abo-Rechnung.
 *
 * Erreichbar über `$billomat->recurrings->emailReceivers`.
 *
 * Endpoints:
 *  - GET    /recurring-email-receivers?recurring_id={id}
 *  - GET    /recurring-email-receivers/{id}
 *  - POST   /recurring-email-receivers
 *  - DELETE /recurring-email-receivers/{id}
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/email-empfaenger/
 *
 * @extends AbstractSubResourceApi<RecurringEmailReceiver>
 */
final class RecurringEmailReceiversApi extends AbstractSubResourceApi
{
    protected const string PLURAL = 'email-receivers';
    protected const string SINGULAR = 'email-receiver';

    public function __construct(BillomatHttpClientInterface $http)
    {
        parent::__construct($http, 'recurring', RecurringEmailReceiver::fromArray(...));
    }

    /**
     * Listet die E-Mail-Empfänger einer Abo-Rechnung.
     *
     * @return list<RecurringEmailReceiver>
     */
    #[NoDiscard]
    public function list(int $recurringId): array
    {
        return $this->listWhere(['recurring_id' => $recurringId]);
    }

    public function create(RecurringEmailReceiverCreateOptions $options): RecurringEmailReceiver
    {
        return $this->createFrom($options->toArray());
    }

    public function delete(int $id): void
    {
        $this->deleteById($id);
    }
}
