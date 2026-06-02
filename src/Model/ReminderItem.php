<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Throwable;

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
        $created = null;
        if (!empty($data['created'])) {
            try {
                $created = new DateTimeImmutable((string) $data['created']);
            } catch (Throwable) {
                $created = null;
            }
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            reminderId: isset($data['reminder_id']) ? (int) $data['reminder_id'] : null,
            articleId: isset($data['article_id']) && '' !== $data['article_id']
                ? (int) $data['article_id']
                : null,
            position: isset($data['position']) ? (int) $data['position'] : null,
            unit: $data['unit'] ?? null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 0.0,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : 0.0,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            created: $created,
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
