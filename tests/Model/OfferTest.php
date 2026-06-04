<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\OfferStatus;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Offer::class)]
final class OfferTest extends TestCase
{
    #[Test]
    public function itHydratesFullOfferFromArray(): void
    {
        $offer = Offer::fromArray([
            'id' => '7',
            'client_id' => '42',
            'contact_id' => '100',
            'offer_number' => 'AN-2025-0007',
            'number' => '7',
            'number_pre' => 'AN-',
            'number_length' => '4',
            'status' => 'ACCEPTED',
            'date' => '2025-04-01',
            'validity_days' => '30',
            'discount_rate' => '2.5',
            'discount_date' => '2025-04-08',
            'discount_days' => '7',
            'discount_amount' => '5.0',
            'title' => 'Angebot Web-Relaunch',
            'label' => 'Projekt X',
            'intro' => 'Vielen Dank für Ihre Anfrage:',
            'note' => 'Angebot gültig 30 Tage.',
            'total_gross' => '1190.0',
            'total_net' => '1000.0',
            'net_gross' => 'NET',
            'reduction' => '10',
            'total_gross_unreduced' => '1300.0',
            'total_net_unreduced' => '1100.0',
            'currency_code' => 'EUR',
            'quote' => '1.0000',
            'free_text_id' => '',
            'template_id' => '5',
            'customerportal_url' => 'https://mycompany.billomat.net/customerportal/offers/show/entityId/7?hash=abc',
            'taxes' => [
                'tax' => [
                    'name' => 'MwSt',
                    'rate' => '19.0',
                    'amount' => '190.0',
                ],
            ],
            'offer-items' => [
                'offer-item' => [
                    [
                        'id' => '1',
                        'offer_id' => '7',
                        'position' => '1',
                        'unit' => 'Stunde',
                        'quantity' => '10',
                        'unit_price' => '100',
                        'title' => 'Beratung',
                        'type' => 'SERVICE',
                    ],
                ],
            ],
        ]);

        self::assertSame(7, $offer->id);
        self::assertSame(42, $offer->clientId);
        self::assertSame(100, $offer->contactId);
        self::assertSame('AN-2025-0007', $offer->offerNumber);
        self::assertSame(OfferStatus::ACCEPTED, $offer->status);
        self::assertInstanceOf(DateTimeImmutable::class, $offer->date);
        self::assertSame(30, $offer->validityDays);
        self::assertSame(2.5, $offer->discountRate);
        self::assertSame('Angebot Web-Relaunch', $offer->title);
        self::assertSame(1190.0, $offer->totalGross);
        self::assertSame(NetGross::NET, $offer->netGross);
        self::assertSame(1.0, $offer->quote);
        self::assertNull($offer->freeTextId);
        self::assertSame(5, $offer->templateId);

        self::assertCount(1, $offer->taxes);
        self::assertSame(19.0, $offer->taxes[0]['rate']);

        self::assertCount(1, $offer->items);
        self::assertContainsOnlyInstancesOf(OfferItem::class, $offer->items);
        self::assertSame('Beratung', $offer->items[0]->title);
        self::assertSame(10.0, $offer->items[0]->quantity);
    }

    #[Test]
    public function itHydratesSingleItemAsList(): void
    {
        $offer = Offer::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'DRAFT',
            'offer-items' => [
                'offer-item' => [
                    'id' => '1',
                    'offer_id' => '1',
                    'quantity' => '1',
                    'unit_price' => '50',
                ],
            ],
        ]);

        self::assertCount(1, $offer->items);
        self::assertSame(50.0, $offer->items[0]->unitPrice);
    }

    #[Test]
    public function itDefaultsToEmptyItemsWhenAbsent(): void
    {
        $offer = Offer::fromArray(['id' => '1', 'client_id' => '1']);

        self::assertSame([], $offer->items);
        self::assertSame([], $offer->taxes);
        self::assertNull($offer->status);
    }

    #[Test]
    public function toArraySerialisesItemsAndTaxes(): void
    {
        $offer = Offer::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'OPEN',
            'currency_code' => 'EUR',
            'taxes' => ['tax' => ['name' => 'MwSt', 'rate' => '19.0', 'amount' => '19.0']],
            'offer-items' => [
                'offer-item' => ['id' => '1', 'offer_id' => '1', 'quantity' => '1', 'unit_price' => '100'],
            ],
        ]);

        $array = $offer->toArray();

        self::assertSame('OPEN', $array['status']);
        self::assertSame('EUR', $array['currency_code']);
        self::assertIsArray($array['taxes']);
        self::assertIsArray($array['taxes']['tax']);
        self::assertCount(1, $array['taxes']['tax']);
        self::assertIsArray($array['offer-items']);
        self::assertIsArray($array['offer-items']['offer-item']);
        self::assertCount(1, $array['offer-items']['offer-item']);
    }
}
