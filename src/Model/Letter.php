<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\LetterStatus;

use const DATE_ATOM;

/**
 * Repräsentiert einen Brief (Letter) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/letters/
 */
final readonly class Letter
{
    public function __construct(
        public ?int $id,
        public int $clientId,
        public ?int $contactId = null,
        public ?DateTimeImmutable $created = null,
        public ?string $letterNumber = null,
        public ?int $number = null,
        public ?string $numberPre = null,
        public ?int $numberLength = null,
        public ?LetterStatus $status = null,
        public ?DateTimeImmutable $date = null,
        public ?string $address = null,
        public ?string $subject = null,
        public ?string $label = null,
        public ?string $intro = null,
        public ?string $note = null,
        public ?int $templateId = null,
        public ?string $customerportalUrl = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: ScalarCaster::toIntOrNull($data['contact_id'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            letterNumber: ScalarCaster::toStringOrNull($data['letter_number'] ?? null),
            number: ScalarCaster::toIntOrNull($data['number'] ?? null),
            numberPre: ScalarCaster::toStringOrNull($data['number_pre'] ?? null),
            numberLength: ScalarCaster::toIntOrNull($data['number_length'] ?? null),
            status: LetterStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            date: ScalarCaster::toDateTimeOrNull($data['date'] ?? null),
            address: ScalarCaster::toStringOrNull($data['address'] ?? null),
            subject: ScalarCaster::toStringOrNull($data['subject'] ?? null),
            label: ScalarCaster::toStringOrNull($data['label'] ?? null),
            intro: ScalarCaster::toStringOrNull($data['intro'] ?? null),
            note: ScalarCaster::toStringOrNull($data['note'] ?? null),
            templateId: ScalarCaster::toIntOrNull($data['template_id'] ?? null),
            customerportalUrl: ScalarCaster::toStringOrNull($data['customerportal_url'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'contact_id' => $this->contactId,
            'created' => $this->created?->format(DATE_ATOM),
            'letter_number' => $this->letterNumber,
            'number' => $this->number,
            'number_pre' => $this->numberPre,
            'number_length' => $this->numberLength,
            'status' => $this->status?->value,
            'date' => $this->date?->format('Y-m-d'),
            'address' => $this->address,
            'subject' => $this->subject,
            'label' => $this->label,
            'intro' => $this->intro,
            'note' => $this->note,
            'template_id' => $this->templateId,
            'customerportal_url' => $this->customerportalUrl,
        ];
    }
}
