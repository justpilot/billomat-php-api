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
            id: (int) ($data['id'] ?? 0),
            offerId: (int) ($data['offer_id'] ?? 0),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
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
