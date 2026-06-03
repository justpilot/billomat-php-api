<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /inbox-documents.
 *
 * Doku: https://www.billomat.com/en/api/inbox-documents/
 */
final class InboxDocumentCreateOptions
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $base64file,
    ) {
    }

    /**
     * Dokumenttyp laut Billomat-Klassifikation (z.B. "other", "invoice",
     * "credit_note"). Default laut Billomat: "other".
     */
    public ?string $documentType = null;

    /**
     * Frei strukturierte Zusatzdaten (Schlüssel-Wert-Paare), die Billomat
     * unverändert speichert.
     *
     * @var array<string, mixed>|null
     */
    public ?array $metadata = null;

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
