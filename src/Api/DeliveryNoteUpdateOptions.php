<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;

/**
 * Typisierter Payload für PUT /delivery-notes/{id}.
 *
 * Nur Lieferscheine im Status DRAFT sind voll editierbar.
 */
final class DeliveryNoteUpdateOptions
{
    public ?int $clientId = null;

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

    public ?NetGross $netGross = null;

    public ?string $currencyCode = null;

    public ?float $quote = null;

    public ?int $invoiceId = null;

    public ?int $offerId = null;

    public ?int $confirmationId = null;

    public ?int $freeTextId = null;

    public ?int $templateId = null;

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
            'net_gross' => $this->netGross?->value,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'invoice_id' => $this->invoiceId,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        return array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);
    }
}
