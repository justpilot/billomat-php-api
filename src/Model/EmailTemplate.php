<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * E-Mail-Vorlage aus den Billomat-Einstellungen.
 *
 * Doku: https://www.billomat.com/api/einstellungen/email-vorlagen/
 *
 * Billomat liefert den Nachrichtentext im Feld `text` (früher `body`), das
 * SDK stellt ihn weiterhin als `$body` bereit. Ein Absender-Feld (`from`)
 * liefert die API nicht mehr; `$fromAddress` bleibt nur aus
 * Kompatibilitätsgründen erhalten und ist in der Regel `null`.
 */
final readonly class EmailTemplate
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $subject = null,
        public ?string $body = null,
        public ?string $fromAddress = null,
        public ?bool $isDefault = null,
        public ?string $type = null,
        public ?bool $bcc = null,
        public ?DateTimeImmutable $created = null,
        public ?DateTimeImmutable $updated = null,
    ) {
    }

    /**
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
            type: ScalarCaster::toStringOrNull($data['type'] ?? null),
            bcc: ScalarCaster::toBoolOrNull($data['bcc'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
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
            'subject' => $this->subject,
            'body' => $this->body,
            'from' => $this->fromAddress,
            'is_default' => $this->isDefault,
            'type' => $this->type,
            'bcc' => $this->bcc,
            'created' => $this->created?->format(DATE_ATOM),
            'updated' => $this->updated?->format(DATE_ATOM),
        ];
    }
}
