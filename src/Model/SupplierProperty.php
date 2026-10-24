<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PropertyType;

/**
 * Definition einer Lieferanten-Eigenschaft.
 *
 * Dieses Model bildet die Struktur von GET /supplier-properties bzw. GET /supplier-properties/{id} ab.
 * Anlegen und Ändern erfolgen über PropertyCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/settings/supplier-properties/
 *
 * Generiert aus tools/codegen/definitions/supplier-properties.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class SupplierProperty
{
    public function __construct(
        /** Interne Billomat-ID des Attributs. */
        public ?int $id,
        /** Name des Attributs. */
        public string $name,
        /**
         * Typ des Attributs.
         *
         * TEXTFIELD = einzeiliges Textfeld, TEXTAREA = mehrzeiliges Textfeld, CHECKBOX = Checkbox
         */
        public ?PropertyType $type,
        /** Standardwert. */
        public ?string $defaultValue = null,
        /**
         * Sortierposition.
         *
         * @deprecated since 3.1 – Billomat liefert `position` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?int $position = null,
        /** Standardwert verwenden, wenn kein anderer Wert gesetzt ist? */
        public ?bool $isNvl = null,
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
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            type: PropertyType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            defaultValue: ScalarCaster::toStringOrNull($data['default_value'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            isNvl: ScalarCaster::toBoolOrNull($data['is_nvl'] ?? null),
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
            'name' => $this->name,
            'type' => $this->type?->value,
            'default_value' => $this->defaultValue,
            'position' => $this->position,
            'is_nvl' => $this->isNvl,
            'customfield' => $this->customfield,
        ];
    }
}
