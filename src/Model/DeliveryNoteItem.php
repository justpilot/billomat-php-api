<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;

use const DATE_ATOM;

/**
 * Position eines Lieferscheins (Delivery Note Item).
 *
 * Doku: https://www.billomat.com/en/api/delivery-notes/items/
 */
final readonly class DeliveryNoteItem
{
    public function __construct(
        public ?int $id,
        public ?int $deliveryNoteId,
        public ?int $articleId,
        public ?int $position,
        public ?string $unit,
        public float $quantity,
        /**
         * @deprecated since 3.1 – Billomat liefert `unit_price` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `0.0`. Wird in 4.0 entfernt.
         */
        public float $unitPrice,
        /**
         * @deprecated since 3.1 – Billomat liefert `tax_name` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $taxName,
        /**
         * @deprecated since 3.1 – Billomat liefert `tax_rate` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $taxRate,
        public ?bool $taxChangedManually,
        public ?string $title,
        public ?string $description,
        /**
         * @deprecated since 3.1 – Billomat liefert `reduction` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?string $reduction,
        public ?InvoiceItemType $type,
        /**
         * @deprecated since 3.1 – Billomat liefert `total_gross` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $totalGross,
        /**
         * @deprecated since 3.1 – Billomat liefert `total_net` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $totalNet,
        /**
         * @deprecated since 3.1 – Billomat liefert `total_gross_unreduced` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $totalGrossUnreduced,
        /**
         * @deprecated since 3.1 – Billomat liefert `total_net_unreduced` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?float $totalNetUnreduced,
        /**
         * @deprecated since 3.1 – Billomat liefert `created` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.
         */
        public ?DateTimeImmutable $created,
        public ?string $customfield = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            deliveryNoteId: ScalarCaster::toIntOrNull($data['delivery_note_id'] ?? null),
            articleId: ScalarCaster::toIntOrNull($data['article_id'] ?? null),
            position: ScalarCaster::toIntOrNull($data['position'] ?? null),
            unit: ScalarCaster::toStringOrNull($data['unit'] ?? null),
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 0.0,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : 0.0,
            taxName: ScalarCaster::toStringOrNull($data['tax_name'] ?? null),
            taxRate: ScalarCaster::toFloatOrNull($data['tax_rate'] ?? null),
            taxChangedManually: ScalarCaster::toBoolOrNull($data['tax_changed_manually'] ?? null),
            title: ScalarCaster::toStringOrNull($data['title'] ?? null),
            description: ScalarCaster::toStringOrNull($data['description'] ?? null),
            reduction: ScalarCaster::toStringOrNull($data['reduction'] ?? null),
            type: isset($data['type']) ? InvoiceItemType::tryFrom((string) $data['type']) : null,
            totalGross: ScalarCaster::toFloatOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toFloatOrNull($data['total_net'] ?? null),
            totalGrossUnreduced: ScalarCaster::toFloatOrNull($data['total_gross_unreduced'] ?? null),
            totalNetUnreduced: ScalarCaster::toFloatOrNull($data['total_net_unreduced'] ?? null),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            customfield: ScalarCaster::toStringOrNull($data['customfield'] ?? null),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'delivery_note_id' => $this->deliveryNoteId,
            'article_id' => $this->articleId,
            'position' => $this->position,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'tax_name' => $this->taxName,
            'tax_rate' => $this->taxRate,
            'tax_changed_manually' => $this->taxChangedManually,
            'title' => $this->title,
            'description' => $this->description,
            'reduction' => $this->reduction,
            'type' => $this->type?->value,
            'total_gross' => $this->totalGross,
            'total_net' => $this->totalNet,
            'total_gross_unreduced' => $this->totalGrossUnreduced,
            'total_net_unreduced' => $this->totalNetUnreduced,
            'created' => $this->created?->format(DATE_ATOM),
            'customfield' => $this->customfield,
        ];
    }
}
