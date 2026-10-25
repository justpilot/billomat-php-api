<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * PDF-Metadaten eines Angebots aus GET /offers/{id}/pdf (JSON-Modus).
 */
final class OfferPdf
{
    public function __construct(
        public int $id,
        public int $offerId,
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
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null) ?? 0,
            offerId: ScalarCaster::toIntOrNull($data['offer_id'] ?? null) ?? 0,
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            filename: ScalarCaster::toStringOrNull($data['filename'] ?? null) ?? '',
            mimeType: ScalarCaster::toStringOrNull($data['mimetype'] ?? null) ?? 'application/pdf',
            fileSize: ScalarCaster::toIntOrNull($data['filesize'] ?? null) ?? 0,
            base64file: ScalarCaster::toStringOrNull($data['base64file'] ?? null) ?? '',
        );
    }

    public function getBinary(): string
    {
        $decoded = base64_decode($this->base64file, true);

        return false === $decoded ? '' : $decoded;
    }
}
