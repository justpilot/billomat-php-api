<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;

use const DATE_ATOM;

/**
 * Angebotsposition (Offer Item) aus der Billomat-API.
 *
 * Doku: https://www.billomat.com/en/api/estimates/items/
 *
 * Hinweis: Der Positionstyp (`type`) verwendet dasselbe Enum wie Rechnungspositionen.
 */
final readonly class OfferItem
{
    public function __construct(
        public ?int $id,
        public ?int $offerId,
        public ?int $articleId,
        public ?int $position,
        public ?string $unit,
        public float $quantity,
        public float $unitPrice,
        public ?string $taxName,
        public ?float $taxRate,
        public ?bool $taxChangedManually,
        public ?string $title,
        public ?string $description,
        public ?string $reduction,
        public ?InvoiceItemType $type,
        public ?float $totalGross,
        public ?float $totalNet,
        public ?float $totalGrossUnreduced,
        public ?float $totalNetUnreduced,
        public ?DateTimeImmutable $created,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ScalarCaster::toIntOrNull($data['id'] ?? null),
            offerId: ScalarCaster::toIntOrNull($data['offer_id'] ?? null),
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
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'offer_id' => $this->offerId,
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
        ];
    }
}
