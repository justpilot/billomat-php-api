<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;

/**
 * Typisierter Payload für PUT /letters/{id}.
 */
final class LetterUpdateOptions
{
    public ?int $clientId = null;

    public ?int $contactId = null;

    public ?int $supplierId = null;

    public ?string $address = null;

    public ?string $numberPre = null;

    public ?int $number = null;

    public ?int $numberLength = null;

    public ?DateTimeImmutable $date = null;

    public ?string $subject = null;

    public ?string $label = null;

    public ?string $intro = null;

    public ?string $note = null;

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
            'supplier_id' => $this->supplierId,
            'address' => $this->address,
            'number_pre' => $this->numberPre,
            'number' => $this->number,
            'number_length' => $this->numberLength,
            'date' => $this->date?->format('Y-m-d'),
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
        ];

        return array_filter($data, static fn (int|string|float|null $v): bool => null !== $v);
    }
}
