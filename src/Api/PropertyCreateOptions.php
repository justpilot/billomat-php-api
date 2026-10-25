<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\PropertyType;

/**
 * Geteilte Options-Klasse für POST und PUT der Attribut-Definitionen
 * (article-properties, client-properties, supplier-properties,
 * incoming-properties, user-properties).
 *
 * Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.
 *
 * Doku:
 * https://www.billomat.com/en/api/settings/client-properties/
 *
 * Generiert aus tools/codegen/definitions/client-properties.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class PropertyCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Name des Attributs.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
    ) {
    }

    /**
     * Typ des Attributs.
     *
     * TEXTFIELD = einzeiliges Textfeld, TEXTAREA = mehrzeiliges Textfeld, CHECKBOX = Checkbox
     *
     * Billomat-Feld: type
     * Typ: ALNUM
     */
    public ?PropertyType $type = null;

    /**
     * Standardwert.
     *
     * Billomat-Feld: default_value
     * Typ: ALNUM
     */
    public ?string $defaultValue = null;

    /**
     * Sortierposition.
     *
     * Billomat-Feld: position
     */
    public ?int $position = null;

    /**
     * Standardwert verwenden, wenn kein anderer Wert gesetzt ist?
     *
     * Billomat-Feld: is_nvl
     * Typ: BOOL
     */
    public ?bool $isNvl = null;

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'type' => $this->type?->value,
            'default_value' => $this->defaultValue,
            'position' => $this->position,
            'is_nvl' => null === $this->isNvl ? null : ($this->isNvl ? 1 : 0),
        ];

        // Null-Felder entfernen
        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
