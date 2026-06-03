<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\SupplyDateType;

/**
 * Payload für PUT /recurrings/{id}.
 *
 * Items können laut Billomat-Doku NICHT über diesen Endpoint editiert werden –
 * dafür ist RecurringItemsApi zuständig. Entsprechend bietet diese Klasse
 * keinen `addItem()`-Helper.
 *
 * Doku: https://www.billomat.com/api/abo-rechnungen/
 */
final class RecurringUpdateOptions
{
    public ?int $clientId = null;
    public ?int $contactId = null;
    public ?string $address = null;
    public ?string $numberPre = null;
    public ?DateTimeImmutable $supplyDate = null;
    public ?SupplyDateType $supplyDateType = null;
    public ?int $dueDays = null;
    public ?int $discountRate = null;
    public ?int $discountDays = null;
    public ?string $name = null;
    public ?string $title = null;
    public ?string $label = null;
    public ?string $intro = null;
    public ?string $note = null;
    public ?string $reduction = null;
    public ?string $currencyCode = null;
    public ?NetGross $netGross = null;
    public ?float $quote = null;
    public ?string $paymentTypes = null;
    public ?RecurringAction $action = null;
    public ?int $cycleNumber = null;
    public ?RecurringCycle $cycle = null;
    public ?int $hour = null;
    public ?DateTimeImmutable $startDate = null;
    public ?DateTimeImmutable $endDate = null;
    public ?int $iterations = null;
    public ?string $emailSender = null;
    public ?string $emailSubject = null;
    public ?string $emailMessage = null;
    public ?int $emailTemplateId = null;
    public ?int $freeTextId = null;
    public ?int $templateId = null;
    public ?DateTimeImmutable $nextCreationDate = null;
    public ?string $emailFilename = null;
    public ?bool $emailBcc = null;
    public ?bool $letterColor = null;
    public ?bool $letterDuplex = null;
    public ?int $letterPaperWeight = null;
    public ?int $offerId = null;
    public ?int $confirmationId = null;

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
            'supply_date' => $this->supplyDate?->format('Y-m-d'),
            'supply_date_type' => $this->supplyDateType?->value,
            'due_days' => $this->dueDays,
            'discount_rate' => $this->discountRate,
            'discount_days' => $this->discountDays,
            'name' => $this->name,
            'title' => $this->title,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'reduction' => $this->reduction,
            'currency_code' => $this->currencyCode,
            'net_gross' => $this->netGross?->value,
            'quote' => $this->quote,
            'payment_types' => $this->paymentTypes,
            'action' => $this->action?->value,
            'cycle_number' => $this->cycleNumber,
            'cycle' => $this->cycle?->value,
            'hour' => $this->hour,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'iterations' => $this->iterations,
            'email_sender' => $this->emailSender,
            'email_subject' => $this->emailSubject,
            'email_message' => $this->emailMessage,
            'email_template_id' => $this->emailTemplateId,
            'free_text_id' => $this->freeTextId,
            'template_id' => $this->templateId,
            'next_creation_date' => $this->nextCreationDate?->format('Y-m-d'),
            'email_filename' => $this->emailFilename,
            'email_bcc' => $this->emailBcc,
            'letter_color' => $this->letterColor,
            'letter_duplex' => $this->letterDuplex,
            'letter_paper_weight' => $this->letterPaperWeight,
            'offer_id' => $this->offerId,
            'confirmation_id' => $this->confirmationId,
        ];

        return array_filter($data, static fn (int|string|float|bool|null $v): bool => null !== $v);
    }
}
