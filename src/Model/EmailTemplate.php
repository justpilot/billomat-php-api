<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\EmailTemplateType;

use const DATE_ATOM;

/**
 * E-Mail-Vorlage aus den Billomat-Einstellungen.
 *
 * Dieses Model bildet die Struktur von GET /email-templates bzw. GET /email-templates/{id} ab.
 *
 * Billomat liefert den Nachrichtentext im Feld `text` (früher `body`), das
 * SDK stellt ihn weiterhin als `$body` bereit.
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/email-vorlagen/
 *
 * Generiert aus tools/codegen/definitions/email-templates.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class EmailTemplate
{
    public function __construct(
        /** Interne Billomat-ID der Vorlage. */
        public ?int $id,
        /** Name der Vorlage. */
        public ?string $name,
        /** Betreff. */
        public ?string $subject = null,
        /** Nachrichtentext. */
        public ?string $body = null,
        /**
         * Absender-Adresse.
         *
         * Billomat liefert dieses Feld nicht mehr, der Wert ist in der Regel `null`.
         */
        public ?string $fromAddress = null,
        /** Standard-Vorlage für den Dokumenttyp? */
        public ?bool $isDefault = null,
        /**
         * Dokumenttyp, für den die Vorlage gilt.
         *
         * Mögliche Werte: INVOICES, OFFERS, CONFIRMATIONS, CREDIT_NOTES, DELIVERY_NOTES, REMINDERS, PAYMENT_THANKS
         */
        public ?EmailTemplateType $type = null,
        /** Erhält der Absender eine BCC-Kopie? */
        public ?bool $bcc = null,
        /** Erstellungszeitpunkt der Vorlage. */
        public ?DateTimeImmutable $created = null,
        /** Zeitpunkt der letzten Änderung. */
        public ?DateTimeImmutable $updated = null,
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
            body: ScalarCaster::toStringOrNull($data['text'] ?? $data['body'] ?? null),
            fromAddress: ScalarCaster::toStringOrNull($data['from'] ?? null),
            isDefault: ScalarCaster::toBoolOrNull($data['is_default'] ?? null),
            type: EmailTemplateType::tryFrom(ScalarCaster::toStringOrNull($data['type'] ?? null) ?? ''),
            bcc: ScalarCaster::toBoolOrNull($data['bcc'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
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
            'body' => $this->body,
            'from' => $this->fromAddress,
            'is_default' => $this->isDefault,
            'type' => $this->type?->value,
            'bcc' => $this->bcc,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
        ];
    }
}
