<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PropertyType;

/**
 * Definition einer Benutzer-Eigenschaft.
 *
 * Dieses Model bildet die Struktur von GET /user-properties bzw. GET /user-properties/{id} ab.
 * Anlegen und Ändern erfolgen über PropertyCreateOptions.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/benutzer-attribute/
 *
 * Generiert aus tools/codegen/definitions/user-properties.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class UserProperty
{
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'name' => true,
        'type' => true,
        'default_value' => true,
        'is_nvl' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID des Attributs. */
        public ?int $id,
        /** Name des Attributs. */
        public string $name,
        /**
         * Typ des Attributs.
         *
         * TEXTFIELD = einzeiliges Textfeld, TEXTAREA = mehrzeiliges Textfeld, CHECKBOX = Checkbox
         */
        public ?PropertyType $type,
        /** Standardwert. */
        public ?string $defaultValue = null,
        /** Standardwert verwenden, wenn kein anderer Wert gesetzt ist? */
        public ?bool $isNvl = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            name: ScalarCaster::toStringOrNull($data['name'] ?? null) ?? '',
            type: PropertyType::fromApi(ScalarCaster::toStringOrNull($data['type'] ?? null)),
            defaultValue: ScalarCaster::toStringOrNull($data['default_value'] ?? null),
            isNvl: ScalarCaster::toBoolOrNull($data['is_nvl'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'name' => $this->name,
            'type' => $this->type?->value,
            'default_value' => $this->defaultValue,
            'is_nvl' => $this->isNvl,
            'customfield' => $this->customfield,
        ];
    }
}
