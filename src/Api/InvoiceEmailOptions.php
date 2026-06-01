<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /invoices/{id}/email.
 *
 * Verschickt eine Rechnung per E-Mail an einen oder mehrere Empfänger.
 *
 * Doku: https://www.billomat.com/en/api/invoices/ (Abschnitt "Send invoice by email")
 *
 * Hinweise:
 * - Ohne explizite Felder verwendet Billomat die im Account hinterlegten Defaults
 *   (Absender, Betreff, Body, BCC, Dateiname). Die Rechnung wird automatisch als
 *   PDF angehängt.
 * - `from` muss eine im Account verifizierte Absenderadresse sein.
 */
final class InvoiceEmailOptions
{
    /**
     * ID der E-Mail-Vorlage aus den Billomat-Einstellungen.
     *
     * Billomat-Feld: email_template_id
     */
    public ?int $emailTemplateId = null;

    /**
     * Absender-Adresse (muss in den Settings hinterlegt/erlaubt sein).
     *
     * Billomat-Feld: from
     */
    public ?string $from = null;

    /**
     * Empfänger (TO).
     *
     * @var list<string>
     */
    public array $to = [];

    /**
     * Empfänger (CC).
     *
     * @var list<string>
     */
    public array $cc = [];

    /**
     * Empfänger (BCC).
     *
     * @var list<string>
     */
    public array $bcc = [];

    /**
     * Betreff. Wenn null, verwendet Billomat die Default-Vorlage.
     *
     * Billomat-Feld: subject
     */
    public ?string $subject = null;

    /**
     * E-Mail-Body. Wenn null, Default-Vorlage.
     *
     * Billomat-Feld: body
     */
    public ?string $body = null;

    /**
     * Dateiname des PDF-Anhangs (ohne `.pdf`).
     *
     * Billomat-Feld: filename
     */
    public ?string $filename = null;

    /**
     * Zusätzliche Anhänge. Jeder Eintrag muss die Schlüssel
     * `filename`, `mimetype` und `base64file` enthalten.
     *
     * @var list<array{filename: string, mimetype: string, base64file: string}>
     */
    public array $attachments = [];

    /**
     * Serialisiert den Payload (ohne den `{"email": ...}`-Wrapper).
     *
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
