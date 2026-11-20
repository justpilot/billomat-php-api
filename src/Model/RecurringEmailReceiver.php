<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

/**
 * E-Mail-Empfänger für eine Abo-Rechnung (TO/CC/BCC).
 *
 * Dokumentation:
 * https://www.billomat.com/api/abo-rechnungen/empfaenger/
 *
 * Generiert aus tools/codegen/definitions/recurring-email-receivers.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class RecurringEmailReceiver
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'recurring_id' => true,
        'type' => true,
        'address' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Empfängers. */
        public int $id,
        /** ID der Abo-Rechnung. */
        public int $recurringId,
        /** E-Mail-Adresse. */
        public string $address,
        /** Empfänger-Typ (To, Cc oder Bcc). */
        public ?RecurringEmailReceiverType $type = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
    ) {
    }

    /**
     * Hydriert das Model aus einem Billomat-Response-Array.
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            recurringId: ScalarCaster::required(ScalarCaster::toIntOrNull($data['recurring_id'] ?? null), self::class, 'recurring_id'),
            address: ScalarCaster::required(ScalarCaster::toStringOrNull($data['address'] ?? null), self::class, 'address'),
            type: RecurringEmailReceiverType::fromApi(ScalarCaster::toStringOrNull($data['type'] ?? null)),
            extra: array_diff_key($data, self::FIELDS),
        );
    }

    /**
     * Exportiert das Model als Array mit Billomat-Feldnamen.
     *
     * Praktisch für Debugging oder Log-Ausgaben.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'id' => $this->id,
            'recurring_id' => $this->recurringId,
            'type' => $this->type?->value,
            'address' => $this->address,
        ];
    }
}
