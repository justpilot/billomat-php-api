<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Mahn-Textbaustein (Mahnstufe).
 *
 * Dieses Model bildet die Struktur von GET /reminder-texts bzw. GET /reminder-texts/{id} ab.
 *
 * Billomat liefert die Reihenfolge als `sorting` (früher `sort`), das SDK
 * stellt sie weiterhin als `$sort` bereit.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/mahnstufen/
 *
 * Generiert aus tools/codegen/definitions/reminder-texts.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class ReminderText
{
    public function __construct(
        /** Interne Billomat-ID der Mahnstufe. */
        public ?int $id,
        /** Name der Mahnstufe. */
        public ?string $name,
        /** Betreff. */
        public ?string $subject = null,
        /** Einleitung. */
        public ?string $header = null,
        /** Anmerkung. */
        public ?string $footer = null,
        /** Reihenfolge der Mahnstufe. */
        public ?int $sort = null,
        /** Name der Mahngebühr (falls vorhanden). */
        public ?string $chargeName = null,
        /** Beschreibung der Mahngebühr (falls vorhanden). */
        public ?string $chargeDescription = null,
        /** Höhe der Mahngebühr (falls vorhanden). */
        public ?float $chargeAmount = null,
        /** Freies Zusatzfeld (siehe Custom-Meta-Attribute). */
        public ?string $customfield = null,
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
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            header: ScalarCaster::toStringOrNull($data['header'] ?? null),
            footer: ScalarCaster::toStringOrNull($data['footer'] ?? null),
            sort: ScalarCaster::toIntOrNull($data['sorting'] ?? $data['sort'] ?? null),
            chargeName: ScalarCaster::toStringOrNull($data['charge_name'] ?? null),
            chargeDescription: ScalarCaster::toStringOrNull($data['charge_description'] ?? null),
            chargeAmount: ScalarCaster::toFloatOrNull($data['charge_amount'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
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
            'id' => $this->id,
            'name' => $this->name,
            'subject' => $this->subject,
            'header' => $this->header,
            'footer' => $this->footer,
            'sort' => $this->sort,
            'charge_name' => $this->chargeName,
            'charge_description' => $this->chargeDescription,
            'charge_amount' => $this->chargeAmount,
            'customfield' => $this->customfield,
        ];
    }
}
