<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /{dokumenttyp}/{id}/mail.
 *
 * Versendet ein Dokument postalisch über den Pixelletter-Service. Rechnungen, Angebote,
 * Auftragsbestätigungen, Gutschriften, Lieferscheine, Briefe und Mahnungen nehmen dieselben
 * Felder an.
 *
 * Doku: https://www.billomat.com/en/api/invoices/ (Abschnitt "Send invoice by post")
 *
 * Hinweise:
 * - Diese Aktion ist kostenpflichtig (Pixelletter-Versand).
 */
final readonly class MailOptions implements WriteOptions
{
    /**
     * @param bool|null                                                           $color       farbiger Druck? (`color`)
     * @param bool|null                                                           $duplex      doppelseitiger Druck? (`duplex`)
     * @param int|null                                                            $paperWeight Papiergewicht in g/m² (80 oder 90; `paper_weight`)
     * @param list<array{filename: string, mimetype: string, base64file: string}> $attachments zusätzliche PDF-Anhänge,
     *                                                                                         die mit ausgedruckt werden
     */
    public function __construct(
        public ?bool $color = null,
        public ?bool $duplex = null,
        public ?int $paperWeight = null,
        public array $attachments = [],
    ) {
    }

    /** Kopie mit geändertem `color`. */
    public function withColor(?bool $color): self
    {
        return clone($this, ['color' => $color]);
    }

    /** Kopie mit geändertem `duplex`. */
    public function withDuplex(?bool $duplex): self
    {
        return clone($this, ['duplex' => $duplex]);
    }

    /** Kopie mit geändertem `paperWeight`. */
    public function withPaperWeight(?int $paperWeight): self
    {
        return clone($this, ['paperWeight' => $paperWeight]);
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
        $data = [
            'color' => null === $this->color ? null : ($this->color ? 1 : 0),
            'duplex' => null === $this->duplex ? null : ($this->duplex ? 1 : 0),
            'paper_weight' => $this->paperWeight,
        ];

        $data = array_filter($data, static fn (string|int|null $v): bool => null !== $v);

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

        return $data;
    }
}
