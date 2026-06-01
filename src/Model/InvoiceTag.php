<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

/**
 * Schlagwort/Tag an einer Rechnung.
 *
 * Doku: https://www.billomat.com/api/rechnungen/schlagworte/
 */
final readonly class InvoiceTag
{
    public function __construct(
        public ?int $id,
        public int $invoiceId,
        public string $name,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            invoiceId: (int) ($data['invoice_id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoiceId,
            'name' => $this->name,
        ];
    }
}
