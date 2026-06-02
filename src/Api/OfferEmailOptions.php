<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /offers/{id}/email.
 *
 * Verschickt ein Angebot per E-Mail. Ohne explizite Felder verwendet Billomat
 * alle Defaults (Absender, Betreff, Body, BCC, Dateiname). Das Angebot wird
 * automatisch als PDF angehängt.
 *
 * Doku: https://www.billomat.com/en/api/estimates/
 */
final class OfferEmailOptions
{
    public ?int $emailTemplateId = null;

    public ?string $from = null;

    /** @var list<string> */
    public array $to = [];

    /** @var list<string> */
    public array $cc = [];

    /** @var list<string> */
    public array $bcc = [];

    public ?string $subject = null;

    public ?string $body = null;

    public ?string $filename = null;

    /**
     * @var list<array{filename: string, mimetype: string, base64file: string}>
     */
    public array $attachments = [];

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $recipients = array_filter([
            'to' => [] !== $this->to ? $this->to : null,
            'cc' => [] !== $this->cc ? $this->cc : null,
            'bcc' => [] !== $this->bcc ? $this->bcc : null,
        ], static fn ($v): bool => null !== $v);

        $data = [
            'email_template_id' => $this->emailTemplateId,
            'from' => $this->from,
            'recipients' => [] !== $recipients ? $recipients : null,
            'subject' => $this->subject,
            'body' => $this->body,
            'filename' => $this->filename,
        ];

        if ([] !== $this->attachments) {
            $data['attachments'] = [
                'attachment' => array_map(
                    static fn (array $a): array => [
                        'filename' => $a['filename'],
                        'mimetype' => $a['mimetype'],
                        'base64file' => $a['base64file'],
                    ],
                    $this->attachments,
                ),
            ];
        }

        return array_filter($data, static fn ($v): bool => null !== $v);
    }
}
