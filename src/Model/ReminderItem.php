<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * Position einer Mahnung (Reminder Item).
 *
 * Reminder-Items repräsentieren die in der Mahnung aufgeführten Rechnungen
 * mit dem ursprünglichen Rechnungsbetrag und den Verzugskosten.
 *
 * Doku: https://www.billomat.com/en/api/reminders/items/
 */
final readonly class ReminderItem
{
    public function __construct(
        public ?int $id,
        public ?int $reminderId,
        public ?int $articleId,
        public ?int $position,
        public ?string $unit,
        public float $quantity,
        public float $unitPrice,
        public ?string $title,
        public ?string $description,
        public ?float $totalGross,
        public ?float $totalNet,
        public ?DateTimeImmutable $created,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            reminderId: ScalarCaster::toIntOrNull($data['reminder_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 0.0,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : 0.0,
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reminder_id' => $this->reminderId,
            'article_id' => $this->articleId,
            'position' => $this->position,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'title' => $this->title,
            'description' => $this->description,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'created' => $this->created?->format(DATE_ATOM),
        ];
    }
}
