<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\LetterStatus;

/**
 * Typisierter Filter für GET /api/letters.
 *
 * Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte
 * werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere
 * Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.
 *
 * Doku:
 * https://www.billomat.com/en/api/letters/
 *
 * Generiert aus tools/codegen/definitions/letters.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class LetterFilter implements ListFilter
{
    public function __construct(
        /**
         * ID des Kunden.
         *
         * Billomat-Filter: client_id
         */
        public ?int $clientId = null,
        /**
         * ID des Kontakts.
         *
         * Billomat-Filter: contact_id
         */
        public ?int $contactId = null,
        /**
         * ID des Lieferanten.
         *
         * Billomat-Filter: supplier_id
         */
        public ?int $supplierId = null,
        /**
         * Briefstatus (DRAFT, COMPLETED). Mehrere Stati können per Komma getrennt werden.
         *
         * Billomat-Filter: status
         *
         * @var list<LetterStatus>
         */
        public array $status = [],
        /**
         * Nur Briefe ab diesem Datum (Format YYYY-MM-DD).
         *
         * Billomat-Filter: from
         */
        public ?DateTimeImmutable $from = null,
        /**
         * Nur Briefe bis zu diesem Datum (Format YYYY-MM-DD).
         *
         * Billomat-Filter: to
         */
        public ?DateTimeImmutable $to = null,
        /**
         * Freitextsuche in der Bezeichnung.
         *
         * Billomat-Filter: label
         */
        public ?string $label = null,
        /**
         * Freitextsuche im Brieftext.
         *
         * Billomat-Filter: intro
         */
        public ?string $intro = null,
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

    /** Kopie mit geändertem `clientId`. */
    public function withClientId(?int $clientId): self
    {
        return clone($this, ['clientId' => $clientId]);
    }

    /** Kopie mit geändertem `contactId`. */
    public function withContactId(?int $contactId): self
    {
        return clone($this, ['contactId' => $contactId]);
    }

    /** Kopie mit geändertem `supplierId`. */
    public function withSupplierId(?int $supplierId): self
    {
        return clone($this, ['supplierId' => $supplierId]);
    }

    /** Kopie mit geändertem `status`. */
    public function withStatus(LetterStatus ...$status): self
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

    /** Kopie mit geändertem `label`. */
    public function withLabel(?string $label): self
    {
        return clone($this, ['label' => $label]);
    }

    /** Kopie mit geändertem `intro`. */
    public function withIntro(?string $intro): self
    {
        return clone($this, ['intro' => $intro]);
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
                'client_id' => $this->clientId,
                'contact_id' => $this->contactId,
                'supplier_id' => $this->supplierId,
                'status' => [] === $this->status ? null : implode(',', array_map(static fn (LetterStatus $v): string => $v->value, $this->status)),
                'from' => $this->from?->format('Y-m-d'),
                'to' => $this->to?->format('Y-m-d'),
                'label' => $this->label,
                'intro' => $this->intro,
                'tags' => [] === $this->tags ? null : implode(',', $this->tags),
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
