<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\IncomingStatus;

/**
 * Typisierter Filter für GET /api/incomings.
 *
 * Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte
 * werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere
 * Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.
 *
 * Doku:
 * https://www.billomat.com/en/api/incomings/
 *
 * Generiert aus tools/codegen/definitions/incomings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class IncomingFilter implements ListFilter
{
    public function __construct(
        /**
         * ID des Lieferanten.
         *
         * Billomat-Filter: supplier_id
         */
        public ?int $supplierId = null,
        /**
         * Eingangsrechnungsnummer.
         *
         * Billomat-Filter: incoming_number
         */
        public ?string $incomingNumber = null,
        /**
         * Eingangsrechnungsstatus (OPEN, PAID, OVERDUE). Mehrere Stati können per Komma getrennt werden und werden ODER-Verknüpft.
         *
         * Billomat-Filter: status
         *
         * @var list<IncomingStatus>
         */
        public array $status = [],
        /**
         * Nur Eingangsrechnung ab diesem Datum (Format YYYY-MM-DD).
         *
         * Billomat-Filter: from
         */
        public ?DateTimeImmutable $from = null,
        /**
         * Nur Eingangsrechnung bis zu diesem Datum (Format YYYY-MM-DD).
         *
         * Billomat-Filter: to
         */
        public ?DateTimeImmutable $to = null,
        /**
         * Freitextsuche im Anmerkungstext.
         *
         * Billomat-Filter: note
         */
        public ?string $note = null,
        /**
         * Kommaseparierte Liste der Schlagworte.
         *
         * Billomat-Filter: tags
         *
         * @var list<string>
         */
        public array $tags = [],
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

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(?int $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `incomingNumber`. */
    public function withIncomingNumber(?string $incomingNumber): self
    {
        return clone($this, ['incomingNumber' => $incomingNumber]);
    }

    /** Kopie mit geändertem `status`. */
    public function withStatus(IncomingStatus ...$status): self
    {
        return clone($this, ['status' => array_values($status)]);
    }

    /** Kopie mit geändertem `from`. */
    public function withFrom(?DateTimeImmutable $from): self
    {
        return clone($this, ['from' => $from]);
    }

    /** Kopie mit geändertem `to`. */
    public function withTo(?DateTimeImmutable $to): self
    {
        return clone($this, ['to' => $to]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `tags`. */
    public function withTags(string ...$tags): self
    {
        return clone($this, ['tags' => array_values($tags)]);
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
                'supplier_id' => $this->supplierId,
                'incoming_number' => $this->incomingNumber,
                'status' => [] === $this->status ? null : implode(',', array_map(static fn (IncomingStatus $v): string => $v->value, $this->status)),
                'from' => $this->from?->format('Y-m-d'),
                'to' => $this->to?->format('Y-m-d'),
                'note' => $this->note,
                'tags' => [] === $this->tags ? null : implode(',', $this->tags),
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
