<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Eintrag aus dem Billomat-Aktivitätsfeed.
 *
 * Bildet die Antwort von GET /activity-feed ab. Aktivitäten dokumentieren
 * Veränderungen an den eigenen Ressourcen (z. B. Statuswechsel an einer
 * Rechnung, Versand einer E-Mail). Ohne `userId` handelt es sich um eine
 * System-Aktivität, sonst um eine vom angegebenen Benutzer ausgelöste.
 *
 * Dokumentation: https://www.billomat.com/api/aktivitaeten/
 */
final readonly class Activity
{
    public function __construct(
        /** Slug der betroffenen Ressource, z. B. "invoices", "delivery-notes". */
        public string $resource,
        /** ID des betroffenen Datensatzes innerhalb der Ressource. */
        public ?int $id,
        /** Zeitpunkt der Aktivität. */
        public ?DateTimeImmutable $date,
        /** Überschrift der Aktivität, z. B. "Rechnung RE123". */
        public ?string $title,
        /** Freitext der Aktivität, z. B. "Status geändert von Entwurf nach offen.". */
        public ?string $text,
        /** Auslösender Benutzer; `null` bei System-Aktivitäten. */
        public ?int $userId,
    ) {
    }

    public function isSystemActivity(): bool
    {
        return null === $this->userId;
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            resource: ScalarCaster::toStringOrNull($data['resource'] ?? null) ?? '',
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            text: ScalarCaster::toStringOrNull($data['text'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'resource' => $this->resource,
            'id' => $this->id,
            'date' => $this->date?->format(DATE_ATOM),
            'title' => $this->title,
            'text' => $this->text,
            'user_id' => $this->userId,
        ];
    }
}
