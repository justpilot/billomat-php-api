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
        public int $id,
        public ?DateTimeImmutable $created,
        public string $filename,
        public string $mimeType,
        public int $fileSize,
        public ?string $base64file = null,
        public ?DateTimeImmutable $updated = null,
        public ?string $customfield = null,
        public ?int $xrechnungExtractionsId = null,
        public ?int $userId = null,
        public ?string $documentType = null,
        public ?int $pageCount = null,
        public ?string $fileUrl = null,
        public ?string $metadata = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            filename: ScalarCaster::toStringOrNull($data['filename'] ?? null) ?? '',
            mimeType: ScalarCaster::toStringOrNull($data['mimetype'] ?? null) ?? 'application/octet-stream',
            fileSize: ScalarCaster::toIntOrNull($data['filesize'] ?? null) ?? 0,
            base64file: ScalarCaster::toStringOrNull($data['base64file'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            xrechnungExtractionsId: ScalarCaster::toIntOrNull($data['xrechnung_extractions_id'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            documentType: ScalarCaster::toStringOrNull($data['document_type'] ?? null),
            pageCount: ScalarCaster::toIntOrNull($data['page_count'] ?? null),
            fileUrl: ScalarCaster::toStringOrNull($data['file_url'] ?? null),
            metadata: ScalarCaster::toStringOrNull($data['metadata'] ?? null),
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
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'xrechnung_extractions_id' => $this->xrechnungExtractionsId,
            'user_id' => $this->userId,
            'document_type' => $this->documentType,
            'page_count' => $this->pageCount,
            'file_url' => $this->fileUrl,
            'metadata' => $this->metadata,
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
