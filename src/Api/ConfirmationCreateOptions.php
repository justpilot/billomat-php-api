<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für POST /confirmations.
 *
 * Doku: https://www.billomat.com/en/api/confirmations/
 */
final class ConfirmationCreateOptions
{
    public function __construct(
        /** ID des Kunden (Pflichtfeld). */
        public int $clientId,
    ) {
    }

    public ?int $contactId = null;

    public ?string $address = null;

    public ?string $numberPre = null;

    public ?int $number = null;

    public ?int $numberLength = null;

    public ?DateTimeImmutable $date = null;

    public ?int $discountRate = null;

    public ?int $discountDays = null;

    public ?DateTimeImmutable $discountDate = null;

    public ?string $title = null;

    public ?string $label = null;

    public ?string $intro = null;

    public ?string $note = null;

    public ?string $reduction = null;

    public ?string $currencyCode = null;

    public ?NetGross $netGross = null;

    public ?float $quote = null;

    /** Quell-Angebot, aus dem die Auftragsbestätigung entsteht. */
    public ?int $offerId = null;

    public ?int $freeTextId = null;

    public ?int $templateId = null;

    /**
     * @var list<ConfirmationItemCreateOptions>
     */
    private array $items = [];

    /**
     * @return $this
     */
    public function addItem(ConfirmationItemCreateOptions $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return list<ConfirmationItemCreateOptions>
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
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'discount_date' => $this->discountDate?->format('Y-m-d'),
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'offer_id' => $this->offerId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        $data = array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);

        if ([] !== $this->items) {
            $data['confirmation-items'] = [
                'confirmation-item' => array_map(
                    static fn (ConfirmationItemCreateOptions $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }
}
