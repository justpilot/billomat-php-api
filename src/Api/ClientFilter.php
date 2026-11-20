<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Filter für GET /api/clients.
 *
 * Nicht gesetzte Felder (null bzw. leere Liste) fehlen im Query; Mehrfachwerte
 * werden kommasepariert gesendet und von Billomat ODER-verknüpft. Weitere
 * Query-Parameter (z. B. benutzerdefinierte Attribute) gehen über `extra`.
 *
 * Doku:
 * https://www.billomat.com/en/api/clients/
 *
 * Generiert aus tools/codegen/definitions/clients.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ClientFilter implements ListFilter
{
    public function __construct(
        /**
         * Firma/Bezeichnung des Kunden.
         *
         * Billomat-Filter: name
         */
        public ?string $name = null,
        /**
         * Kundennummer.
         *
         * Billomat-Filter: client_number
         */
        public ?string $clientNumber = null,
        /**
         * E-Mail-Adresse.
         *
         * Billomat-Filter: email
         */
        public ?string $email = null,
        /**
         * Vorname Ansprechpartner.
         *
         * Billomat-Filter: first_name
         */
        public ?string $firstName = null,
        /**
         * Nachname Ansprechpartner.
         *
         * Billomat-Filter: last_name
         */
        public ?string $lastName = null,
        /**
         * Ländercode nach ISO 3166 Alpha-2.
         *
         * Billomat-Filter: country_code
         */
        public ?string $countryCode = null,
        /**
         * Notiz.
         *
         * Billomat-Filter: note
         */
        public ?string $note = null,
        /**
         * ID einer Rechnung für diesen Kunden, mehrere Werte kommasepariert.
         *
         * Billomat-Filter: invoice_id
         *
         * @var list<int>
         */
        public array $invoiceId = [],
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

    /** Kopie mit geändertem `name`. */
    public function withName(?string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `clientNumber`. */
    public function withClientNumber(?string $clientNumber): self
    {
        return clone($this, ['clientNumber' => $clientNumber]);
    }

    /** Kopie mit geändertem `email`. */
    public function withEmail(?string $email): self
    {
        return clone($this, ['email' => $email]);
    }

    /** Kopie mit geändertem `firstName`. */
    public function withFirstName(?string $firstName): self
    {
        return clone($this, ['firstName' => $firstName]);
    }

    /** Kopie mit geändertem `lastName`. */
    public function withLastName(?string $lastName): self
    {
        return clone($this, ['lastName' => $lastName]);
    }

    /** Kopie mit geändertem `countryCode`. */
    public function withCountryCode(?string $countryCode): self
    {
        return clone($this, ['countryCode' => $countryCode]);
    }

    /** Kopie mit geändertem `note`. */
    public function withNote(?string $note): self
    {
        return clone($this, ['note' => $note]);
    }

    /** Kopie mit geändertem `invoiceId`. */
    public function withInvoiceId(int ...$invoiceId): self
    {
        return clone($this, ['invoiceId' => array_values($invoiceId)]);
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
                'name' => $this->name,
                'client_number' => $this->clientNumber,
                'email' => $this->email,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'country_code' => $this->countryCode,
                'note' => $this->note,
                'invoice_id' => [] === $this->invoiceId ? null : implode(',', $this->invoiceId),
                'tags' => [] === $this->tags ? null : implode(',', $this->tags),
                'order_by' => $this->orderBy?->toQuery(),
                'page' => $this->page,
                'per_page' => $this->perPage,
            ], static fn (mixed $v): bool => null !== $v),
            ...$this->extra,
        ];
    }
}
