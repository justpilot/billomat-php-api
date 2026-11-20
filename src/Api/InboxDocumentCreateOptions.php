<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use InvalidArgumentException;
use Justpilot\Billomat\Model\Enum\InboxDocumentMetadataKey;
use Justpilot\Billomat\Model\Enum\InboxDocumentType;

/**
 * Payload für POST /inbox-documents.
 *
 * Doku: https://www.billomat.com/api/eingangsrechnungen/inbox/
 */
final readonly class InboxDocumentCreateOptions implements WriteOptions
{
    /**
     * @param string                     $filename     Dateiname
     * @param string                     $mimeType     MIME-Typ (`mimetype`)
     * @param string                     $base64File   Dateiinhalt, Base64-kodiert
     * @param InboxDocumentType|null     $documentType Dokumenttyp; Default laut Billomat: {@see InboxDocumentType::OTHER}
     * @param array<string, string>|null $metadata     Metadaten als Schlüssel → Wert; erlaubt sind nur die Schlüssel
     *                                                 aus {@see InboxDocumentMetadataKey}, z. B.
     *                                                 `[InboxDocumentMetadataKey::SENDER_NAME->value => 'ACME GmbH']`
     */
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $base64File,
        public ?InboxDocumentType $documentType = null,
        public ?array $metadata = null,
    ) {
    }

    /** Kopie mit geändertem `documentType`. */
    public function withDocumentType(?InboxDocumentType $documentType): self
    {
        return clone($this, ['documentType' => $documentType]);
    }

    /**
     * Kopie mit geänderten Metadaten.
     *
     * @param array<string, string>|null $metadata Schlüssel aus {@see InboxDocumentMetadataKey} → Wert
     */
    public function withMetadata(?array $metadata): self
    {
        return clone($this, ['metadata' => $metadata]);
    }

    /**
     * @return array<string,mixed>
     *
     * @throws InvalidArgumentException bei einem Metadaten-Schlüssel, den Billomat nicht kennt
     */
    public function toArray(): array
    {
        $data = [
            'filename' => $this->filename,
            'mimetype' => $this->mimeType,
            'base64file' => $this->base64File,
            'document_type' => $this->documentType?->value,
            'metadata' => null === $this->metadata ? null : $this->metadataToWire($this->metadata),
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }

    /**
     * Billomat erwartet die Metadaten als Liste von Schlüssel-Wert-Paaren unter `data`.
     *
     * @param array<string, string> $metadata
     *
     * @return array{data: list<array{key: string, value: string}>}
     */
    private function metadataToWire(array $metadata): array
    {
        $pairs = [];
        foreach ($metadata as $key => $value) {
            if (null === InboxDocumentMetadataKey::tryFrom($key)) {
                throw new InvalidArgumentException(\sprintf('Unknown inbox document metadata key "%s"; use a case of %s.', $key, InboxDocumentMetadataKey::class));
            }
            $pairs[] = ['key' => $key, 'value' => $value];
        }

        return ['data' => $pairs];
    }
}
