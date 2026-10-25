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
    /** Billomat-Felder, die das Model abbildet. */
    private const array FIELDS = [
        'id' => true,
        'name' => true,
        'subject' => true,
        'header' => true,
        'footer' => true,
        'sorting' => true,
        'sort' => true,
        'charge_name' => true,
        'charge_description' => true,
        'charge_amount' => true,
        'customfield' => true,
    ];

    /** @param array<string, mixed> $extra */
    public function __construct(
        /** Interne Billomat-ID der Mahnstufe. */
        public int $id,
        /** Name der Mahnstufe. */
        public ?string $name = null,
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
        public ?Money $chargeAmount = null,
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
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            header: ScalarCaster::toStringOrNull($data['header'] ?? null),
            footer: ScalarCaster::toStringOrNull($data['footer'] ?? null),
            sort: ScalarCaster::toIntOrNull($data['sorting'] ?? $data['sort'] ?? null),
            chargeName: ScalarCaster::toStringOrNull($data['charge_name'] ?? null),
            chargeDescription: ScalarCaster::toStringOrNull($data['charge_description'] ?? null),
            chargeAmount: ScalarCaster::toMoneyOrNull($data['charge_amount'] ?? null),
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
            'subject' => $this->subject,
            'header' => $this->header,
            'footer' => $this->footer,
            'sort' => $this->sort,
            'charge_name' => $this->chargeName,
            'charge_description' => $this->chargeDescription,
            'charge_amount' => $this->chargeAmount?->toString(),
            'customfield' => $this->customfield,
        ];
    }
}
