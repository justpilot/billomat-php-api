<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

/**
 * Typisierter Payload für PUT /recurring-email-receivers/{id}.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 * Die Abo-Rechnung eines Empfängers lässt sich nicht ändern.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/empfaenger/
 *
 * Generiert aus tools/codegen/definitions/recurring-email-receivers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class RecurringEmailReceiverUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Empfänger-Typ (To, Cc oder Bcc).
         *
         * Billomat-Feld: type
         * Typ: ALNUM
         */
        public RecurringEmailReceiverType|Clear|null $type = null,
        /**
         * E-Mail-Adresse.
         *
         * Billomat-Feld: address
         * Typ: ALNUM
         */
        public string|Clear|null $address = null,
    ) {
    }

    /** Kopie mit geändertem `type`. */
    public function withType(RecurringEmailReceiverType|Clear|null $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `address`. */
    public function withAddress(string|Clear|null $address): self
    {
        return clone($this, ['address' => $address]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter([
                'type' => $this->type instanceof RecurringEmailReceiverType ? $this->type->value : $this->type,
                'address' => $this->address,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
