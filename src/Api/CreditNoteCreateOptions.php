<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für POST /credit-notes.
 *
 * Doku: https://www.billomat.com/en/api/credit-notes/
 */
final class CreditNoteCreateOptions
{
    public function __construct(
        public int $clientId,
    ) {
    }

    public ?int $contactId = null;

    public ?string $address = null;

    public ?string $numberPre = null;

    public ?int $number = null;

    public ?int $numberLength = null;

    public ?DateTimeImmutable $date = null;

    public ?string $title = null;

    public ?string $label = null;

    public ?string $intro = null;

    public ?string $note = null;

    public ?string $reduction = null;

    public ?string $currencyCode = null;

    public ?NetGross $netGross = null;

    public ?float $quote = null;

    /** Quell-Rechnung für Korrekturgutschrift. */
    public ?int $invoiceId = null;

    public ?int $freeTextId = null;

    public ?int $templateId = null;

    /**
     * @var list<CreditNoteItemCreateOptions>
     */
    private array $items = [];

    /**
     * @return $this
     */
    public function addItem(CreditNoteItemCreateOptions $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return list<CreditNoteItemCreateOptions>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'address' => $this->address,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'invoice_id' => $this->invoiceId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        $data = array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['credit-note-items'] = [
                'credit-note-item' => array_map(
                    static fn (CreditNoteItemCreateOptions $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }
}
