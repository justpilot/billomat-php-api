<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Filter für GET /api/supplier-property-values.
 *
 * Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte
 * werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere
 * Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.
 *
 * Doku:
 * https://www.billomat.com/api/lieferanten/attribute/
 *
 * Generiert aus tools/codegen/definitions/supplier-property-values.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class SupplierPropertyValueFilter implements ListFilter
{
    public function __construct(
        /**
         * ID eines speziellen Attributs.
         *
         * Billomat-Filter: supplier_property_id
         */
        public ?int $supplierPropertyId = null,
        /**
         * Wert des Attributs.
         *
         * Billomat-Filter: value
         */
        public ?string $value = null,
        /** Sortierung, z. B. `OrderBy::desc('date')->thenAsc('name')`. */
        public ?OrderBy $orderBy = null,
        /**
         * Seite (ab 1); `iterateAll()` setzt sie selbst.
         *
         * Billomat-Filter: page
         */
        public ?int $page = null,
        /**
         * Einträge pro Seite (Billomat: Standard 100, maximal 1000).
         *
         * Billomat-Filter: per_page
         */
        public ?int $perPage = null,
        /**
         * Weitere Query-Parameter; überschreiben gleichnamige Felder.
         *
         * @var array<string, scalar|array<scalar>>
         */
        public array $extra = [],
    ) {
    }

    /** Kopie mit geändertem `supplierPropertyId`. */
    public function withSupplierPropertyId(?int $supplierPropertyId): self
    {
        return clone($this, ['supplierPropertyId' => $supplierPropertyId]);
    }

    /** Kopie mit geändertem `value`. */
    public function withValue(?string $value): self
    {
        return clone($this, ['value' => $value]);
    }

    /** Kopie mit geändertem `orderBy`. */
    public function withOrderBy(?OrderBy $orderBy): self
    {
        return clone($this, ['orderBy' => $orderBy]);
    }

    /** Kopie mit geändertem `page`. */
    public function withPage(?int $page): self
    {
        return clone($this, ['page' => $page]);
    }

    /** Kopie mit geändertem `perPage`. */
    public function withPerPage(?int $perPage): self
    {
        return clone($this, ['perPage' => $perPage]);
    }

    /**
     * Kopie mit weiteren Query-Parametern.
     *
     * @param array<string, scalar|array<scalar>> $extra
     */
    public function withExtra(array $extra): self
    {
        return clone($this, ['extra' => $extra]);
    }

    public function toQuery(): array
    {
        return [
            ...array_filter([
                'supplier_property_id' => $this->supplierPropertyId,
                'value' => $this->value,
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
