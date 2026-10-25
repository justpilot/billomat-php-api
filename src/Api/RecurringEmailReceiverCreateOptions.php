<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

/**
 * Typisierter Payload für POST /recurring-email-receivers.
 *
 * Der Empfänger wird zu den bereits vorhandenen Empfängern der Abo-Rechnung hinzugefügt.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/empfaenger/
 *
 * Generiert aus tools/codegen/definitions/recurring-email-receivers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class RecurringEmailReceiverCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID der Abo-Rechnung.
         *
         * Billomat-Feld: recurring_id
         * Typ: INT
         */
        public int $recurringId,
        /**
         * Empfänger-Typ (To, Cc oder Bcc).
         *
         * Billomat-Feld: type
         * Typ: ALNUM
         */
        public RecurringEmailReceiverType $type,
        /**
         * E-Mail-Adresse.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string $address,
    ) {
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'recurring_id' => $this->recurringId,
            'type' => $this->type->value,
            'address' => $this->address,
        ];
    }
}
