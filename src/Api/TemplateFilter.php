<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateType;

/**
 * Typisierter Filter für GET /api/templates.
 *
 * Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte
 * werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere
 * Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/vorlagen/
 *
 * Generiert aus tools/codegen/definitions/templates.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class TemplateFilter implements ListFilter
{
    public function __construct(
        /**
         * Dokumenttyp, auf die sich die Vorlage bezieht (INVOICE, OFFER, CONFIRMATION, REMINDER, DELIVERY_NOTE, CREDIT_NOTE, LETTER). Mehrere Typbezeichnungen können per Komma getrennt werden und werden ODER-Verknüpft.
         *
         * Billomat-Filter: type
         *
         * @var list<TemplateDocumentType>
         */
        public array $type = [],
        /**
         * Typ der Vorlage (DEFINED = über den Editor konfigurierte Vorlage, UPLOADED = hochgeladene Vorlage).
         *
         * Billomat-Filter: template_type
         */
        public ?TemplateType $templateType = null,
        /**
         * Standardvorlage (1 = ja, 0 = nein).
         *
         * Billomat-Filter: is_default
         */
        public ?bool $isDefault = null,
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

    /** Kopie mit geändertem `type`. */
    public function withType(TemplateDocumentType ...$type): self
    {
        return clone($this, ['type' => array_values($type)]);
    }

    /** Kopie mit geändertem `templateType`. */
    public function withTemplateType(?TemplateType $templateType): self
    {
        return clone($this, ['templateType' => $templateType]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(?bool $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
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
                'type' => [] === $this->type ? null : implode(',', array_map(static fn (TemplateDocumentType $v): string => $v->value, $this->type)),
                'template_type' => $this->templateType?->value,
                'is_default' => null === $this->isDefault ? null : ($this->isDefault ? 1 : 0),
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
