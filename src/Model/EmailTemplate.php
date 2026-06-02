<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * E-Mail-Vorlage aus den Billomat-Einstellungen.
 *
 * Doku: https://www.billomat.com/en/api/settings/email-templates/
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
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? null,
            subject: $data['subject'] ?? null,
            body: $data['body'] ?? null,
            fromAddress: $data['from'] ?? null,
            isDefault: isset($data['is_default']) ? (bool) (int) $data['is_default'] : null,
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
        ];
    }
}
