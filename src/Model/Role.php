<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Mitarbeiter-Rolle samt zugeordneter Zugriffsrechte.
 *
 * Quelle: https://www.billomat.com/api/einstellungen/rollen/
 *
 * Die einzelnen Rechte-Felder (z.B. `articles`, `clients`, …) liefern die
 * Werte `READ`, `UPDATE`, `DELETE` oder einen leeren String (= keine Rechte).
 * Da Billomat die Liste der Rechte-Felder erweitern kann, werden sie nicht als
 * einzelne Properties exponiert, sondern als `permissions`-Map zugänglich
 * gemacht.
 */
final readonly class Role
{
    /**
     * @param array<string,?string> $permissions
     */
    public function __construct(
        public ?int $id,
        public ?string $name = null,
        public array $permissions = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $permissionKeys = [
            'articles', 'clients', 'offers', 'confirmations', 'invoices',
            'credit_notes', 'delivery_notes', 'reminders',
            'settings_my_account', 'settings_documents', 'settings_configuration',
            'settings_administration', 'settings_addons', 'settings_my_addons',
        ];

        $permissions = [];
        foreach ($data as $key => $value) {
            if (!\is_string($key)) {
                continue;
            }
            if ('id' === $key || 'name' === $key) {
                continue;
            }
            if (\in_array($key, $permissionKeys, true) || str_starts_with($key, 'settings_')) {
                $permissions[$key] = ScalarCaster::toStringOrNull($value);
            }
        }

        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            permissions: $permissions,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => $this->permissions,
        ];
    }
}
