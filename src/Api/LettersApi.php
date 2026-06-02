<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Letter;
use Justpilot\Billomat\Model\LetterPdf;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * API-Wrapper für die Billomat-Letters-Ressource (Briefe).
 *
 * Doku: https://www.billomat.com/en/api/letters/
 *
 * Endpoints:
 *  - GET    /letters
 *  - GET    /letters/{id}
 *  - POST   /letters
 *  - PUT    /letters/{id}
 *  - DELETE /letters/{id}
 *  - PUT    /letters/{id}/complete
 *  - PUT    /letters/{id}/cancel
 *  - PUT    /letters/{id}/clear
 *  - PUT    /letters/{id}/undo
 *  - POST   /letters/{id}/email
 *  - GET    /letters/{id}/pdf
 *  - PUT    /letters/{id}/upload-signature
 *  - PUT    /letters/{id}/upload         (kompletten Brief-PDF hochladen)
 */
final class LettersApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Letter>
     */
    public function list(array $filters = []): array
    {
        $data = $this->getJson('/letters', $filters);

        $node = $data['letters']['letter'] ?? [];

        if (null === $node || [] === $node) {
            return [];
        }

        if (\is_array($node) && array_is_list($node)) {
            $rows = $node;
        } elseif (\is_array($node)) {
            $rows = [$node];
        } else {
            $rows = [];
        }

        /** @var list<Letter> $models */
        $models = array_map(
            Letter::fromArray(...),
            $rows,
        );

        return $models;
    }

    public function get(int $id): ?Letter
    {
        $data = $this->getJsonOrNull("/letters/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['letter'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Letter::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(LetterCreateOptions $options): Letter
    {
        $payload = ['letter' => $options->toArray()];

        $data = $this->postJson('/letters', $payload);

        $created = $data['letter'] ?? null;

        if (!\is_array($created)) {
            throw new RuntimeException('Unexpected response from Billomat when creating letter.');
        }

        return Letter::fromArray($created);
    }

    public function update(int $id, LetterUpdateOptions $options): Letter
    {
        $payload = ['letter' => $options->toArray()];

        $data = $this->putJson("/letters/{$id}", $payload);

        $row = $data['letter'] ?? null;
        if (!\is_array($row)) {
            throw new RuntimeException('Unexpected response from Billomat when updating letter.');
        }

        return Letter::fromArray($row);
    }

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['letter' => $body];

        $this->putVoid("/letters/{$id}/complete", $payload);

        return true;
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/letters/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $this->putVoid("/letters/{$id}/cancel");

        return true;
    }

    public function clear(int $id): bool
    {
        $this->putVoid("/letters/{$id}/clear");

        return true;
    }

    public function undo(int $id): bool
    {
        $this->putVoid("/letters/{$id}/undo");

        return true;
    }

    public function email(int $id, ?LetterEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/letters/{$id}/email", $payload);

        return true;
    }

    /**
     * Lädt das komplette Brief-PDF hoch (z.B. wenn das Layout extern erzeugt wurde).
     *
     * Entspricht PUT /letters/{id}/upload.
     */
    public function upload(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/letters/{$id}/upload", $payload);

        return true;
    }

    /** Lädt eine unterschriebene PDF-Version des Briefs hoch. */
    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/letters/{$id}/upload-signature", $payload);

        return true;
    }

    /**
     * @return LetterPdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): LetterPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/letters/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/letters/{$id}/pdf", $query);

        $pdfData = $data['pdf'] ?? null;

        if (!\is_array($pdfData)) {
            throw new RuntimeException('Unexpected response from Billomat when fetching letter PDF.');
        }

        return LetterPdf::fromArray($pdfData);
    }
}
