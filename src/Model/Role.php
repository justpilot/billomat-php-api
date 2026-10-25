<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\AccessLevel;

use const ARRAY_FILTER_USE_KEY;
use const DATE_ATOM;

/**
 * Mitarbeiter-Rolle samt zugeordneter Zugriffsrechte.
 *
 * Quelle: https://www.billomat.com/api/einstellungen/rollen/
 *
 * Die einzelnen Rechte-Felder (z.B. `articles`, `clients`, …) liefern die
 * Werte `READ`, `UPDATE`, `DELETE` oder einen leeren String (= keine Rechte).
 * Da Billomat die Liste der Rechte-Felder erweitern kann, werden sie nicht als
 * einzelne Properties exponiert, sondern als `permissions`-Map zugänglich
 * gemacht. Bereiche ohne Rechte (oder mit unbekanntem Wert) fehlen in der Map.
 */
final readonly class Role
{
    /**
     * Bekannte Rechte-Bereiche; dazu zählt jedes Feld, das mit `settings_` beginnt.
     *
     * @var list<string>
     */
    public const array AREAS = [
        'articles', 'clients', 'suppliers', 'offers', 'confirmations', 'invoices',
        'credit_notes', 'delivery_notes', 'reminders', 'incomings', 'letters',
        'banking', 'net_income_statement', 'accounting_export',
        'settings_my_account', 'settings_documents', 'settings_configuration',
        'settings_administration', 'settings_addons', 'settings_my_addons',
    ];

    /**
     * @param array<string, AccessLevel> $permissions Bereich (z. B. `articles`) → Zugriffsstufe
     */
    public function __construct(
        public int $id,
        public ?string $name = null,
        public array $permissions = [],
        /** Erstellungszeitpunkt der Rolle. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $areas = array_filter(
            $data,
            static fn (string|int $key): bool => \in_array($key, self::AREAS, true) || str_starts_with((string) $key, 'settings_'),
            ARRAY_FILTER_USE_KEY,
        );

        return new self(
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            permissions: ListCaster::toEnumMap($areas, AccessLevel::class),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
        );
    }

    /**
     * Exportiert die Rolle mit Billomat-Feldnamen; die Rechte stehen wie bei Billomat
     * als einzelne Felder (`articles` => `DELETE`, …) auf oberster Ebene.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            ...array_map(static fn (AccessLevel $level): string => $level->value, $this->permissions),
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
        ];
    }
}
