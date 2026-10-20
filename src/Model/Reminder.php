<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\ReminderStatus;

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
        public ?DateTimeImmutable $updated = null,
        public ?string $customfield = null,
        public ?float $paidAmount = null,
        public ?float $openAmount = null,
        public ?int $reminderLevel = null,
        public ?string $reminderLevelName = null,
        public ?bool $isOld = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
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
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            invoiceId: ScalarCaster::toIntOrNull($data['invoice_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            reminderNumber: ScalarCaster::toStringOrNull($data['reminder_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: ReminderStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            dueDays: ScalarCaster::toIntOrNull($data['due_days'] ?? null),
            dueDate: ScalarCaster::toDateTimeOrNull($data['due_date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            currencyCode: ScalarCaster::toStringOrNull($data['currency_code'] ?? null),
            quote: ScalarCaster::toFloatOrNull($data['quote'] ?? null),
            reminderTextId: ScalarCaster::toIntOrNull($data['reminder_text_id'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            items: $items,
            updated: ScalarCaster::toDateTimeOrNull($data['updated'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
            paidAmount: ScalarCaster::toFloatOrNull($data['paid_amount'] ?? null),
            openAmount: ScalarCaster::toFloatOrNull($data['open_amount'] ?? null),
            reminderLevel: ScalarCaster::toIntOrNull($data['reminder_level'] ?? null),
            reminderLevelName: ScalarCaster::toStringOrNull($data['reminder_level_name'] ?? null),
            isOld: ScalarCaster::toBoolOrNull($data['is_old'] ?? null),
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
            'updated' => $this->updated?->format(DATE_ATOM),
            'customfield' => $this->customfield,
            'paid_amount' => $this->paidAmount,
            'open_amount' => $this->openAmount,
            'reminder_level' => $this->reminderLevel,
            'reminder_level_name' => $this->reminderLevelName,
            'is_old' => $this->isOld,
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
}
