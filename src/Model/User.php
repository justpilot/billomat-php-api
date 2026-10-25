<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Benutzer/Mitarbeiter aus der Billomat-API.
 *
 * Dokumentation:
 * https://www.billomat.com/en/api/users/
 *
 * Generiert aus tools/codegen/definitions/users.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class User
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'email' => true,
        'first_name' => true,
        'last_name' => true,
        'salutation' => true,
        'role_id' => true,
        'created' => true,
        'updated' => true,
        'customfield' => true,
        'locale' => true,
        'timezone' => true,
        'is_account_owner' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Benutzers. */
        public ?int $id,
        /** E-Mail-Adresse (Login). */
        public ?string $email,
        /** Vorname. */
        public ?string $firstName = null,
        /** Nachname. */
        public ?string $lastName = null,
        /** Anrede. */
        public ?string $salutation = null,
        /** ID der Rolle des Benutzers. */
        public ?int $roleId = null,
        /** Erstellungszeitpunkt des Benutzers. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
        /** Sprache der Oberfläche (z. B. "de_DE"). */
        public ?string $locale = null,
        /** Zeitzone (z. B. "Europe/Berlin"). */
        public ?string $timezone = null,
        /** Ist der Benutzer Inhaber des Accounts? */
        public ?bool $isAccountOwner = null,
        /**
         * Felder, die Billomat liefert, das SDK aber (noch) nicht abbildet – roh, wie in der Response.
         *
         * Neue Billomat-Felder gehen so nicht verloren; toArray() gibt sie mit aus.
         */
        public array $extra = [],
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
            email: ScalarCaster::toStringOrNull($data['email'] ?? null),
            firstName: ScalarCaster::toStringOrNull($data['first_name'] ?? null),
            lastName: ScalarCaster::toStringOrNull($data['last_name'] ?? null),
            salutation: ScalarCaster::toStringOrNull($data['salutation'] ?? null),
            roleId: ScalarCaster::toIntOrNull($data['role_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            locale: ScalarCaster::toStringOrNull($data['locale'] ?? null),
            timezone: ScalarCaster::toStringOrNull($data['timezone'] ?? null),
            isAccountOwner: ScalarCaster::toBoolOrNull($data['is_account_owner'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
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
            ...$this->extra,
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'salutation' => $this->salutation,
            'role_id' => $this->roleId,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'is_account_owner' => $this->isAccountOwner,
        ];
    }
}
