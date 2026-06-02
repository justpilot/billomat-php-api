<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\LetterStatus;
use Throwable;

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
        $created = self::parseDateTime($data['created'] ?? null);
        $date = self::parseDateTime($data['date'] ?? null);

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            clientId: (int) ($data['client_id'] ?? 0),
            contactId: isset($data['contact_id']) && '' !== $data['contact_id']
                ? (int) $data['contact_id']
                : null,
            created: $created,
            letterNumber: $data['letter_number'] ?? null,
            number: isset($data['number']) && '' !== $data['number']
                ? (int) $data['number']
                : null,
            numberPre: $data['number_pre'] ?? null,
            numberLength: isset($data['number_length']) ? (int) $data['number_length'] : null,
            status: LetterStatus::fromApi(isset($data['status']) ? (string) $data['status'] : null),
            date: $date,
            address: $data['address'] ?? null,
            subject: $data['subject'] ?? null,
            label: $data['label'] ?? null,
            intro: $data['intro'] ?? null,
            note: $data['note'] ?? null,
            templateId: isset($data['template_id']) && '' !== $data['template_id']
                ? (int) $data['template_id']
                : null,
            customerportalUrl: $data['customerportal_url'] ?? null,
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
