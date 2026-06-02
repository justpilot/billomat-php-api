<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Mahn-Textbaustein.
 *
 * Doku: https://www.billomat.com/en/api/settings/reminder-texts/
 */
final readonly class ReminderText
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $subject = null,
        public ?string $header = null,
        public ?string $footer = null,
        public ?int $dueDays = null,
        public ?int $sort = null,
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
            header: ScalarCaster::toStringOrNull($data['header'] ?? null),
            footer: ScalarCaster::toStringOrNull($data['footer'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            sort: ScalarCaster::toIntOrNull($data['sort'] ?? null),
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
            'header' => $this->header,
            'footer' => $this->footer,
            'due_days' => $this->dueDays,
            'sort' => $this->sort,
        ];
    }
}
