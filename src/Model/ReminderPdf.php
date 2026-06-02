<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Throwable;

/**
 * PDF-Metadaten einer Mahnung aus GET /reminders/{id}/pdf.
 */
final class ReminderPdf
{
    public function __construct(
        public int $id,
        public int $reminderId,
        public ?DateTimeImmutable $created,
        public string $filename,
        public string $mimeType,
        public int $fileSize,
        public string $base64file,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = null;
        $createdRaw = $data['created'] ?? null;

        if (\is_string($createdRaw) && '' !== $createdRaw) {
            try {
                $created = new DateTimeImmutable($createdRaw);
            } catch (Throwable) {
                $created = null;
            }
        }

        return new self(
            id: (int) ($data['id'] ?? 0),
            reminderId: (int) ($data['reminder_id'] ?? 0),
            created: $created,
            filename: (string) ($data['filename'] ?? ''),
            mimeType: (string) ($data['mimetype'] ?? 'application/pdf'),
            fileSize: (int) ($data['filesize'] ?? 0),
            base64file: (string) ($data['base64file'] ?? ''),
        );
    }

    public function getBinary(): string
    {
        $decoded = base64_decode($this->base64file, true);

        return false === $decoded ? '' : $decoded;
    }
}
