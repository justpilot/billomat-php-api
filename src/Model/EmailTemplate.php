<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            name: ScalarCaster::toStringOrNull($data['name'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            body: ScalarCaster::toStringOrNull($data['body'] ?? null),
            fromAddress: ScalarCaster::toStringOrNull($data['from'] ?? null),
            isDefault: ScalarCaster::toBoolOrNull($data['is_default'] ?? null),
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
