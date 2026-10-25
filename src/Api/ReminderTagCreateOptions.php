<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /reminder-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/mahnungen/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/reminder-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ReminderTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: reminder_id
         * Typ: INT
         */
        public int $reminderId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
    ) {
    }

    /** Kopie mit geändertem `reminderId`. */
    public function withReminderId(int $reminderId): self
    {
        return clone($this, ['reminderId' => $reminderId]);
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'reminder_id' => $this->reminderId,
            'name' => $this->name,
        ];
    }
}
