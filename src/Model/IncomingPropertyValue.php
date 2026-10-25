<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PropertyType;

use const DATE_ATOM;

/**
 * Wert einer Eigenschaft einer Eingangsrechnung.
 *
 * Dieses Model bildet die Struktur von GET /incoming-property-values?incoming_id={id} bzw. GET /incoming-property-values/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/eingangsrechnungen/attribute/
 *
 * Generiert aus tools/codegen/definitions/incoming-property-values.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingPropertyValue
{
    public function __construct(
        /** Interne Billomat-ID des Eigenschaftswerts. */
        public ?int $id,
        /** ID des Datensatzes, zu dem der Wert gehört. */
        public int $incomingId,
        /** ID der Eigenschaft. */
        public int $incomingPropertyId,
        /** Typ der Eigenschaft (TEXTFIELD, TEXTAREA oder CHECKBOX). */
        public ?PropertyType $type,
        /** Name der Eigenschaft. */
        public ?string $name,
        /** Wert der Eigenschaft. */
        public mixed $value,
        /** Erstellungszeitpunkt. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            incomingId: ScalarCaster::toIntOrNull($data['incoming_id'] ?? null) ?? 0,
            incomingPropertyId: ScalarCaster::toIntOrNull($data['incoming_property_id'] ?? null) ?? 0,
            type: PropertyType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            value: $data['value'] ?? null,
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'id' => $this->id,
            'incoming_id' => $this->incomingId,
            'incoming_property_id' => $this->incomingPropertyId,
            'type' => $this->type?->value,
            'name' => $this->name,
            'value' => $this->value,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
        ];
    }
}
