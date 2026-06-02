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
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'mimetype' => $this->mimeType,
            'base64file' => $this->base64file,
        ];
    }
}
