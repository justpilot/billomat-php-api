<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Throwable;

use const DATE_ATOM;
use const FILTER_VALIDATE_BOOLEAN;

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
        $created = null;
        if (!empty($data['created'])) {
            try {
                $created = new DateTimeImmutable((string) $data['created']);
            } catch (Throwable) {
                $created = null;
            }
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            offerId: isset($data['offer_id']) ? (int) $data['offer_id'] : null,
            articleId: isset($data['article_id']) && '' !== $data['article_id']
                ? (int) $data['article_id']
                : null,
            position: isset($data['position']) ? (int) $data['position'] : null,
            unit: $data['unit'] ?? null,
            quantity: isset($data['quantity']) ? (float) $data['quantity'] : 0.0,
            unitPrice: isset($data['unit_price']) ? (float) $data['unit_price'] : 0.0,
            taxName: $data['tax_name'] ?? null,
            taxRate: isset($data['tax_rate']) ? (float) $data['tax_rate'] : null,
            taxChangedManually: isset($data['tax_changed_manually'])
                ? filter_var($data['tax_changed_manually'], FILTER_VALIDATE_BOOLEAN)
                : null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            reduction: $data['reduction'] ?? null,
            type: isset($data['type']) ? InvoiceItemType::tryFrom((string) $data['type']) : null,
            totalGross: isset($data['total_gross']) ? (float) $data['total_gross'] : null,
            totalNet: isset($data['total_net']) ? (float) $data['total_net'] : null,
            totalGrossUnreduced: isset($data['total_gross_unreduced'])
                ? (float) $data['total_gross_unreduced']
                : null,
            totalNetUnreduced: isset($data['total_net_unreduced'])
                ? (float) $data['total_net_unreduced']
                : null,
            created: $created,
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
