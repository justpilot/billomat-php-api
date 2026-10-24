<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Schlagwort (Tag) an einem Lieferanten.
 *
 * Dieses Model bildet die Struktur von GET /supplier-tags?supplier_id={id} bzw. GET /supplier-tags/{id} ab.
 *
 * Dokumentation:
 * https://www.billomat.com/api/lieferanten/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/supplier-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class SupplierTag
{
    public function __construct(
        /** Interne Billomat-ID des Schlagworts. */
        public ?int $id,
        /** ID des Datensatzes, an dem das Schlagwort hängt. */
        public int $supplierId,
        /** Name des Schlagworts. */
        public string $name,
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
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null) ?? 0,
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
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
            'supplier_id' => $this->supplierId,
            'name' => $this->name,
            'customfield' => $this->customfield,
        ];
    }
}
