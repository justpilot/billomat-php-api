<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InboxDocumentType;

use const DATE_ATOM;

/**
 * Posteingangs-Dokument (Inbox Document).
 *
 * Doku: https://www.billomat.com/api/eingangsrechnungen/inbox/
 */
final readonly class InboxDocument
{
    /** Wire-Felder, die das Model kennt; alle anderen landen in {@see $extra}. */
    private const array FIELDS = [
        'id' => true, 'created' => true, 'filename' => true, 'mimetype' => true, 'filesize' => true,
        'base64file' => true, 'updated' => true, 'customfield' => true, 'xrechnung_extractions_id' => true,
        'user_id' => true, 'document_type' => true, 'page_count' => true, 'file_url' => true, 'metadata' => true,
    ];

    public function __construct(
        public int $id,
        public ?DateTimeImmutable $created,
        public string $filename,
        public string $mimeType,
        public int $fileSize,
        public ?string $base64File = null,
        public ?DateTimeImmutable $updated = null,
        public ?string $customField = null,
        public ?int $xrechnungExtractionsId = null,
        public ?int $userId = null,
        public ?InboxDocumentType $documentType = null,
        public ?int $pageCount = null,
        public ?string $fileUrl = null,
        /**
         * Metadaten als Schlüssel → Wert (Schlüssel siehe {@see Enum\InboxDocumentMetadataKey}); leer, wenn keine gesetzt sind.
         *
         * @var array<string, string>
         */
        public array $metadata = [],
        /**
         * Felder aus der Antwort, die das Model (noch) nicht kennt.
         *
         * @var array<string, mixed>
         */
        public array $extra = [],
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
            filename: ScalarCaster::required(ScalarCaster::toStringOrNull($data['filename'] ?? null), self::class, 'filename'),
            mimeType: ScalarCaster::toStringOrNull($data['mimetype'] ?? null) ?? 'application/octet-stream',
            fileSize: ScalarCaster::required(ScalarCaster::toIntOrNull($data['filesize'] ?? null), self::class, 'filesize'),
            base64File: ScalarCaster::toStringOrNull($data['base64file'] ?? null),
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customField: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            xrechnungExtractionsId: ScalarCaster::toIntOrNull($data['xrechnung_extractions_id'] ?? null),
            userId: ScalarCaster::toIntOrNull($data['user_id'] ?? null),
            documentType: InboxDocumentType::fromApi(ScalarCaster::toStringOrNull($data['document_type'] ?? null)),
            pageCount: ScalarCaster::toIntOrNull($data['page_count'] ?? null),
            fileUrl: ScalarCaster::toStringOrNull($data['file_url'] ?? null),
            metadata: self::metadataFromWire($data['metadata'] ?? null),
            extra: array_diff_key($data, self::FIELDS),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'id' => $this->id,
            'created' => $this->created?->format(DATE_ATOM),
            'filename' => $this->filename,
            'mimetype' => $this->mimeType,
            'filesize' => $this->fileSize,
            'base64file' => $this->base64File,
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customField,
            'xrechnung_extractions_id' => $this->xrechnungExtractionsId,
            'user_id' => $this->userId,
            'document_type' => $this->documentType?->value,
            'page_count' => $this->pageCount,
            'file_url' => $this->fileUrl,
            'metadata' => [] === $this->metadata ? '' : ['data' => array_map(
                static fn (string $key, string $value): array => ['key' => $key, 'value' => $value],
                array_keys($this->metadata),
                array_values($this->metadata),
            )],
        ];
    }

    /**
     * Billomat liefert `{"data": [{"key": …, "value": …}, …]}`, bei einem Eintrag ohne Liste, ohne Einträge `""`.
     *
     * @return array<string, string>
     */
    private static function metadataFromWire(mixed $metadata): array
    {
        if (!\is_array($metadata) || !\is_array($metadata['data'] ?? null)) {
            return [];
        }
        $pairs = $metadata['data'];
        if (!array_is_list($pairs)) {
            $pairs = [$pairs];
        }

        $result = [];
        foreach ($pairs as $pair) {
            if (!\is_array($pair)) {
                continue;
            }
            $key = ScalarCaster::toStringOrNull($pair['key'] ?? null);
            $value = ScalarCaster::toStringOrNull($pair['value'] ?? null);
            if (null !== $key && null !== $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function getBinary(): string
    {
        if (null === $this->base64File || '' === $this->base64File) {
            return '';
        }

        $decoded = base64_decode($this->base64File, true);

        return false === $decoded ? '' : $decoded;
    }
}
