<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Payload für POST /invoice-tags.
 *
 * Doku: https://www.billomat.com/api/rechnungen/schlagworte/
 */
final class InvoiceTagCreateOptions
{
    public function __construct(
        public int $invoiceId,
        public string $name,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoiceId,
            'name' => $this->name,
        ];
    }
}
