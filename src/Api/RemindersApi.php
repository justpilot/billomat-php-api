<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\ReminderPdf;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * API-Wrapper für die Billomat-Reminders-Ressource (Mahnungen).
 *
 * Doku: https://www.billomat.com/en/api/reminders/
 *
 * Endpoints:
 *  - GET    /reminders
 *  - GET    /reminders/{id}
 *  - POST   /reminders
 *  - PUT    /reminders/{id}
 *  - DELETE /reminders/{id}
 *  - PUT    /reminders/{id}/complete
 *  - PUT    /reminders/{id}/cancel
 *  - POST   /reminders/{id}/email
 *  - GET    /reminders/{id}/pdf
 *  - PUT    /reminders/{id}/upload-signature
 */
final class RemindersApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $filters
     *
     * @return list<Reminder>
     */
    public function list(array $filters = []): array
    {
        return $this->listResource('/reminders', 'reminders', 'reminder', Reminder::fromArray(...), $filters);
    }

    public function get(int $id): ?Reminder
    {
        $data = $this->getJsonOrNull("/reminders/{$id}");

        if (null === $data) {
            return null;
        }

        $row = $data['reminder'] ?? null;

        if (!\is_array($row)) {
            return null;
        }

        return Reminder::fromArray($row);
    }

    /**
     * @throws ValidationException
     * @throws AuthenticationException
     * @throws HttpException
     */
    public function create(ReminderCreateOptions $options): Reminder
    {
        $payload = ['reminder' => $options->toArray()];

        $data = $this->postJson('/reminders', $payload);

        return Reminder::fromArray($this->unwrapEnvelope($data, 'reminder', 'creating reminder'));
    }

    public function update(int $id, ReminderUpdateOptions $options): Reminder
    {
        $payload = ['reminder' => $options->toArray()];

        $data = $this->putJson("/reminders/{$id}", $payload);

        return Reminder::fromArray($this->unwrapEnvelope($data, 'reminder', 'updating reminder'));
    }

    public function complete(int $id, ?int $templateId = null): bool
    {
        $body = [];

        if (null !== $templateId) {
            $body['template_id'] = $templateId;
        }

        $payload = ['reminder' => $body];

        $this->putVoid("/reminders/{$id}/complete", $payload);

        return true;
    }

    public function delete(int $id): bool
    {
        $this->deleteVoid("/reminders/{$id}");

        return true;
    }

    public function cancel(int $id): bool
    {
        $this->putVoid("/reminders/{$id}/cancel");

        return true;
    }

    public function email(int $id, ?ReminderEmailOptions $options = null): bool
    {
        $payload = ['email' => $options?->toArray() ?? []];

        $this->postJson("/reminders/{$id}/email", $payload);

        return true;
    }

    public function uploadSignature(int $id, string $base64Pdf): bool
    {
        $payload = ['upload' => ['base64file' => $base64Pdf]];

        $this->putVoid("/reminders/{$id}/upload-signature", $payload);

        return true;
    }

    /**
     * @return ReminderPdf|string PDF-Model im JSON-Modus, binär im Raw-Modus
     */
    public function pdf(int $id, ?InvoicePdfType $type = null, bool $rawPdf = false): ReminderPdf|string
    {
        $query = [];

        if ($type instanceof InvoicePdfType) {
            $query['type'] = $type->value;
        }

        if ($rawPdf) {
            $query['format'] = 'pdf';

            $response = $this->http->request('GET', "/reminders/{$id}/pdf", $query);

            try {
                return $response->getContent();
            } catch (HttpExceptionInterface $e) {
                throw $this->mapHttpException($e);
            }
        }

        $data = $this->getJson("/reminders/{$id}/pdf", $query);

        return ReminderPdf::fromArray($this->unwrapEnvelope($data, 'pdf', 'fetching reminder PDF'));
    }
}
