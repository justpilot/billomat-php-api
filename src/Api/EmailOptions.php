<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /{dokumente}/{id}/email.
 *
 * Gilt für Rechnungen, Angebote, Auftragsbestätigungen, Gutschriften, Lieferscheine,
 * Mahnungen und Briefe. Ohne explizite Felder verwendet Billomat die im Account
 * hinterlegten Defaults (Absender, Betreff, Body, BCC, Dateiname); das Dokument
 * wird automatisch als PDF angehängt.
 *
 * Doku: https://www.billomat.com/en/api/invoices/ (Abschnitt "Send invoice by email")
 */
final readonly class EmailOptions implements WriteOptions
{
    /**
     * @param int|null                                                            $emailTemplateId ID der E-Mail-Vorlage aus den Billomat-Einstellungen (`email_template_id`)
     * @param string|null                                                         $from            Absender-Adresse; muss im Account verifiziert sein
     * @param list<string>                                                        $to              Empfänger (TO)
     * @param list<string>                                                        $cc              Empfänger (CC)
     * @param list<string>                                                        $bcc             Empfänger (BCC)
     * @param string|null                                                         $subject         Betreff
     * @param string|null                                                         $body            Nachrichtentext
     * @param string|null                                                         $filename        Dateiname des angehängten PDFs (ohne Endung)
     * @param list<array{filename: string, mimetype: string, base64file: string}> $attachments     zusätzliche Anhänge
     */
    public function __construct(
        public ?int $emailTemplateId = null,
        public ?string $from = null,
        public array $to = [],
        public array $cc = [],
        public array $bcc = [],
        public ?string $subject = null,
        public ?string $body = null,
        public ?string $filename = null,
        public array $attachments = [],
    ) {
    }

    /** Kopie mit geändertem `emailTemplateId`. */
    public function withEmailTemplateId(?int $emailTemplateId): self
    {
        return clone($this, ['emailTemplateId' => $emailTemplateId]);
    }

    /** Kopie mit geändertem `from`. */
    public function withFrom(?string $from): self
    {
        return clone($this, ['from' => $from]);
    }

    /**
     * Kopie mit geänderten Empfängern (TO).
     *
     * @param list<string> $to
     */
    public function withTo(array $to): self
    {
        return clone($this, ['to' => $to]);
    }

    /**
     * Kopie mit geänderten Empfängern (CC).
     *
     * @param list<string> $cc
     */
    public function withCc(array $cc): self
    {
        return clone($this, ['cc' => $cc]);
    }

    /**
     * Kopie mit geänderten Empfängern (BCC).
     *
     * @param list<string> $bcc
     */
    public function withBcc(array $bcc): self
    {
        return clone($this, ['bcc' => $bcc]);
    }

    /** Kopie mit geändertem `subject`. */
    public function withSubject(?string $subject): self
    {
        return clone($this, ['subject' => $subject]);
    }

    /** Kopie mit geändertem `body`. */
    public function withBody(?string $body): self
    {
        return clone($this, ['body' => $body]);
    }

    /** Kopie mit geändertem `filename`. */
    public function withFilename(?string $filename): self
    {
        return clone($this, ['filename' => $filename]);
    }

    /**
     * Kopie mit geänderten Anhängen.
     *
     * @param list<array{filename: string, mimetype: string, base64file: string}> $attachments
     */
    public function withAttachments(array $attachments): self
    {
        return clone($this, ['attachments' => $attachments]);
    }

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
