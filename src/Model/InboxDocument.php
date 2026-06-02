<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

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
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            filename: (string) ($data['filename'] ?? ''),
            mimeType: (string) ($data['mimetype'] ?? 'application/octet-stream'),
            fileSize: (int) ($data['filesize'] ?? 0),
            base64file: ScalarCaster::toStringOrNull($data['base64file'] ?? null),
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
