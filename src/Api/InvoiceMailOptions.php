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
 * - `recipientAddress` ist die mehrzeilige Postanschrift. Bleibt sie leer,
 *   verwendet Billomat die auf der Rechnung gespeicherte Adresse.
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
     * Papiergewicht in g/m² (z. B. "80", "100").
     *
     * Billomat-Feld: paper_weight
     */
    public ?string $paperWeight = null;

    /**
     * Abweichende Empfängeradresse (mehrzeilig).
     *
     * Billomat-Feld: recipient_address
     */
    public ?string $recipientAddress = null;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'color' => null === $this->color ? null : ($this->color ? 1 : 0),
            'duplex' => null === $this->duplex ? null : ($this->duplex ? 1 : 0),
            'paper_weight' => $this->paperWeight,
            'recipient_address' => $this->recipientAddress,
        ];

        return array_filter($data, static fn (string|int|null $v): bool => null !== $v);
    }
}
