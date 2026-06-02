<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Throwable;

use const DATE_ATOM;

/**
 * Posteingangs-Dokument (Inbox Document).
 *
 * Doku: https://www.billomat.com/en/api/inbox-documents/
 */
final readonly class InboxDocument
{
    public function __construct(
        public ?int $id,
        public ?DateTimeImmutable $created,
        public string $filename,
        public string $mimeType,
        public int $fileSize,
        public ?string $base64file = null,
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
            created: $created,
            filename: (string) ($data['filename'] ?? ''),
            mimeType: (string) ($data['mimetype'] ?? 'application/octet-stream'),
            fileSize: (int) ($data['filesize'] ?? 0),
            base64file: $data['base64file'] ?? null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'filename' => $this->filename,
            'mimetype' => $this->mimeType,
            'filesize' => $this->fileSize,
            'base64file' => $this->base64file,
        ];
    }

    public function getBinary(): string
    {
        if (null === $this->base64file || '' === $this->base64file) {
            return '';
        }

        $decoded = base64_decode($this->base64file, true);

        return false === $decoded ? '' : $decoded;
    }
}
