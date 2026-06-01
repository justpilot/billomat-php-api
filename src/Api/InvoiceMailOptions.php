<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /invoices/{id}/mail.
 *
 * Versendet eine Rechnung postalisch über den Pixelletter-Service.
 *
 * Doku: https://www.billomat.com/en/api/invoices/ (Abschnitt "Send invoice by post")
 *
 * Hinweise:
 * - Diese Aktion ist kostenpflichtig (Pixelletter-Versand).
 */
final class InvoiceMailOptions
{
    /**
     * Farbiger Druck?
     *
     * Billomat-Feld: color
     */
    public ?bool $color = null;

    /**
     * Doppelseitiger Druck?
     *
     * Billomat-Feld: duplex
     */
    public ?bool $duplex = null;

    /**
     * Papiergewicht in g/m² (z. B. "80", "90").
     *
     * Billomat-Feld: paper_weight
     */
    public ?string $paperWeight = null;

    /**
     * Zusätzliche PDF-Anhänge, die mit ausgedruckt werden. Jeder Eintrag muss
     * die Schlüssel `filename`, `mimetype` und `base64file` enthalten.
     *
     * @var list<array{filename: string, mimetype: string, base64file: string}>
     */
    public array $attachments = [];

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
