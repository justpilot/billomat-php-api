<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\ReminderStatus;
use Throwable;

use const DATE_ATOM;

/**
 * Repräsentiert eine Mahnung (Reminder) aus der Billomat-API.
 *
 * Eine Mahnung referenziert immer eine bestehende Rechnung.
 *
 * Doku: https://www.billomat.com/en/api/reminders/
 */
final readonly class Reminder
{
    /**
     * @param list<ReminderItem> $items
     */
    public function __construct(
        public ?int $id,
        public int $clientId,
        public ?int $contactId = null,
        public ?int $invoiceId = null,
        public ?DateTimeImmutable $created = null,
        public ?string $reminderNumber = null,
        public ?int $number = null,
        public ?string $numberPre = null,
        public ?int $numberLength = null,
        public ?ReminderStatus $status = null,
        public ?DateTimeImmutable $date = null,
        public ?int $dueDays = null,
        public ?DateTimeImmutable $dueDate = null,
        public ?string $address = null,
        public ?string $subject = null,
        public ?string $label = null,
        public ?string $intro = null,
        public ?string $note = null,
        public ?float $totalGross = null,
        public ?float $totalNet = null,
        public ?string $currencyCode = null,
        public ?float $quote = null,
        public ?int $reminderTextId = null,
        public ?int $templateId = null,
        public array $items = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = self::parseDateTime($data['created'] ?? null);
        $date = self::parseDateTime($data['date'] ?? null);
        $dueDate = self::parseDateTime($data['due_date'] ?? null);

        $items = [];
        if (isset($data['reminder-items']['reminder-item'])) {
            $rawItems = $data['reminder-items']['reminder-item'];

            if (isset($rawItems['id'])) {
                $rawItems = [$rawItems];
            }

            if (\is_array($rawItems)) {
                $items = array_map(
                    ReminderItem::fromArray(...),
                    $rawItems,
                );
            }
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: isset($data['contact_id']) && '' !== $data['contact_id']
                ? (int) $data['contact_id']
                : null,
            invoiceId: isset($data['invoice_id']) && '' !== $data['invoice_id']
                ? (int) $data['invoice_id']
                : null,
            created: $created,
            reminderNumber: $data['reminder_number'] ?? null,
            number: isset($data['number']) && '' !== $data['number']
                ? (int) $data['number']
                : null,
            numberPre: $data['number_pre'] ?? null,
            numberLength: isset($data['number_length']) ? (int) $data['number_length'] : null,
            status: ReminderStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            date: $date,
            dueDays: isset($data['due_days']) ? (int) $data['due_days'] : null,
            dueDate: $dueDate,
            address: $data['address'] ?? null,
            subject: $data['subject'] ?? null,
            label: $data['label'] ?? null,
            intro: $data['intro'] ?? null,
            note: $data['note'] ?? null,
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            currencyCode: $data['currency_code'] ?? null,
            quote: isset($data['quote']) ? (float) $data['quote'] : null,
            reminderTextId: isset($data['reminder_text_id']) && '' !== $data['reminder_text_id']
                ? (int) $data['reminder_text_id']
                : null,
            templateId: isset($data['template_id']) && '' !== $data['template_id']
                ? (int) $data['template_id']
                : null,
            items: $items,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'invoice_id' => $this->invoiceId,
            'created' => $this->created?->format(DATE_ATOM),
            'reminder_number' => $this->reminderNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'due_days' => $this->dueDays,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'address' => $this->address,
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'currency_code' => $this->currencyCode,
            'quote' => $this->quote,
            'reminder_text_id' => $this->reminderTextId,
            'template_id' => $this->templateId,
        ];

        if ([] !== $this->items) {
            $data['reminder-items'] = [
                'reminder-item' => array_map(
                    static fn (ReminderItem $item): array => $item->toArray(),
                    $this->items,
                ),
            ];
        }

        return $data;
    }

    private static function parseDateTime(?string $value): ?DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }
}
