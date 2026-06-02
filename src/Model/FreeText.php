<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Freitext-Baustein (verwendbar als title/label/intro/note auf Dokumenten).
 *
 * Doku: https://www.billomat.com/en/api/settings/free-texts/
 */
final readonly class FreeText
{
    public function __construct(
        public ?int $id,
        public ?string $title,
        public ?string $label = null,
        public ?string $intro = null,
        public ?string $note = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            title: $data['title'] ?? null,
            label: $data['label'] ?? null,
            intro: $data['intro'] ?? null,
            note: $data['note'] ?? null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
        ];
    }
}
