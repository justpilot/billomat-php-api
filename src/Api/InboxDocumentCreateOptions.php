<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /inbox-documents.
 *
 * Doku: https://www.billomat.com/en/api/inbox-documents/
 */
final readonly class InboxDocumentCreateOptions implements WriteOptions
{
    /**
     * @param string                    $filename     Dateiname
     * @param string                    $mimeType     MIME-Typ (`mimetype`)
     * @param string                    $base64file   Dateiinhalt, Base64-kodiert
     * @param string|null               $documentType Dokumenttyp laut Billomat-Klassifikation (z. B. "other", "invoice",
     *                                                "credit_note"); Default laut Billomat: "other"
     * @param array<string, mixed>|null $metadata     frei strukturierte Zusatzdaten (Schlüssel-Wert-Paare),
     *                                                die Billomat unverändert speichert
     */
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $base64file,
        public ?string $documentType = null,
        public ?array $metadata = null,
    ) {
    }

    /** Kopie mit geändertem `documentType`. */
    public function withDocumentType(?string $documentType): self
    {
        return clone($this, ['documentType' => $documentType]);
    }

    /**
     * Kopie mit geänderten Zusatzdaten.
     *
     * @param array<string, mixed>|null $metadata
     */
    public function withMetadata(?array $metadata): self
    {
        return clone($this, ['metadata' => $metadata]);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'filename' => $this->filename,
            'mimetype' => $this->mimeType,
            'base64file' => $this->base64file,
            'document_type' => $this->documentType,
            'metadata' => $this->metadata,
        ];

        return array_filter($data, static fn (mixed $v): bool => null !== $v);
    }
}
