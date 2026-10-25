<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\Enum\RecurringCycle;

/**
 * Typisierter Filter für GET /api/recurrings.
 *
 * Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte
 * werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere
 * Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.
 *
 * Doku:
 * https://www.billomat.com/api/abo-rechnungen/
 *
 * Generiert aus tools/codegen/definitions/recurrings.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class RecurringFilter implements ListFilter
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
         * Der Name der Abo-Rechnung.
         *
         * Billomat-Filter: name
         */
        public ?string $name = null,
        /**
         * Zahlart (z.B. CASH, BANK_TRANSFER, PAYPAL, …). Mehrere Zahlarten können per Komma getrennt werden und werden ODER-Verknüpft. Eine Übersicht aller Zahlarten findet man bei der API-Dokumentation für Zahlungen.
         *
         * Billomat-Filter: payment_type
         *
         * @var list<InvoicePaymentType>
         */
        public array $paymentType = [],
        /**
         * Intervall (DAILY, WEEKLY, MONTHLY, YEARLY).
         *
         * Billomat-Filter: cycle
         */
        public ?RecurringCycle $cycle = null,
        /**
         * Freitextsuche in der Bezeichnung.
         *
         * Billomat-Filter: label
         */
        public ?string $label = null,
        /**
         * Freitextsuche im Einleitungstext.
         *
         * Billomat-Filter: intro
         */
        public ?string $intro = null,
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

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `paymentType`. */
    public function withPaymentType(InvoicePaymentType ...$paymentType): self
    {
        return clone($this, ['paymentType' => array_values($paymentType)]);
    }

    /** Kopie mit geändertem `cycle`. */
    public function withCycle(?RecurringCycle $cycle): self
    {
        return clone($this, ['cycle' => $cycle]);
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
                'client_id' => $this->clientId,
                'contact_id' => $this->contactId,
                'name' => $this->name,
                'payment_type' => [] === $this->paymentType ? null : implode(',', array_map(static fn (InvoicePaymentType $v): string => $v->value, $this->paymentType)),
                'cycle' => $this->cycle?->value,
                'label' => $this->label,
                'intro' => $this->intro,
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
