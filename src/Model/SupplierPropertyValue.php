<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PropertyType;

use const DATE_ATOM;

/**
 * Wert einer Lieferanten-Eigenschaft.
 *
 * Dieses Model bildet die Struktur von GET /supplier-property-values?supplier_id={id} bzw. GET /supplier-property-values/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/lieferanten/attribute/
 *
 * Generiert aus tools/codegen/definitions/supplier-property-values.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class SupplierPropertyValue
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'supplier_id' => true,
        'supplier_property_id' => true,
        'type' => true,
        'name' => true,
        'value' => true,
        'created' => true,
        'updated' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Eigenschaftswerts. */
        public ?int $id,
        /** ID des Datensatzes, zu dem der Wert gehört. */
        public int $supplierId,
        /** ID der Eigenschaft. */
        public int $supplierPropertyId,
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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null) ?? 0,
            supplierPropertyId: ScalarCaster::toIntOrNull($data['supplier_property_id'] ?? null) ?? 0,
            type: PropertyType::fromApi(ScalarCaster::toStringOrNull($data['type'] ?? null)),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            value: $data['value'] ?? null,
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'supplier_id' => $this->supplierId,
            'supplier_property_id' => $this->supplierPropertyId,
            'type' => $this->type?->value,
            'name' => $this->name,
            'value' => $this->value,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
        ];
    }
}
