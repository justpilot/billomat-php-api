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
final readonly class RecurringEmailReceiverCreateOptions implements WriteOptions
{
    public function __construct(
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

    /** Kopie mit geändertem `type`. */
    public function withType(RecurringEmailReceiverType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(string $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'address' => $this->address,
        ];
    }
}
