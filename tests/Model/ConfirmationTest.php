<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationItem;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Confirmation::class)]
final class ConfirmationTest extends TestCase
{
    #[Test]
    public function itHydratesFullConfirmationFromArray(): void
    {
        $confirmation = Confirmation::fromArray([
            'id' => '12',
            'client_id' => '42',
            'offer_id' => '7',
            'confirmation_number' => 'AB-2025-0012',
            'status' => 'OPEN',
            'date' => '2025-04-15',
            'currency_code' => 'EUR',
            'net_gross' => 'NET',
            'total_gross' => '1190.0',
            'total_net' => '1000.0',
            'quote' => '1.0',
            'taxes' => [
                'tax' => [
                    'name' => 'MwSt',
                    'rate' => '19.0',
                    'amount' => '190.0',
                ],
            ],
            'confirmation-items' => [
                'confirmation-item' => [
                    [
                        'id' => '1',
                        'confirmation_id' => '12',
                        'position' => '1',
                        'quantity' => '5',
                        'unit_price' => '200',
                        'title' => 'Lizenz',
                    ],
                ],
            ],
        ]);

        self::assertSame(12, $confirmation->id);
        self::assertSame(42, $confirmation->clientId);
        self::assertSame(7, $confirmation->offerId);
        self::assertSame('AB-2025-0012', $confirmation->confirmationNumber);
        self::assertSame(ConfirmationStatus::OPEN, $confirmation->status);
        self::assertInstanceOf(DateTimeImmutable::class, $confirmation->date);
        self::assertSame(NetGross::NET, $confirmation->netGross);
        self::assertSame(1190.0, $confirmation->totalGross);

        self::assertCount(1, $confirmation->items);
        self::assertContainsOnlyInstancesOf(ConfirmationItem::class, $confirmation->items);
        self::assertSame('Lizenz', $confirmation->items[0]->title);
        self::assertSame(5.0, $confirmation->items[0]->quantity);
    }

    #[Test]
    public function itHandlesSingleItemArrayShape(): void
    {
        $confirmation = Confirmation::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'DRAFT',
            'confirmation-items' => [
                'confirmation-item' => [
                    'id' => '1',
                    'confirmation_id' => '1',
                    'quantity' => '2',
                    'unit_price' => '50',
                ],
            ],
        ]);

        self::assertCount(1, $confirmation->items);
        self::assertSame(2.0, $confirmation->items[0]->quantity);
    }

    #[Test]
    public function itDefaultsToEmptyItemsWhenAbsent(): void
    {
        $confirmation = Confirmation::fromArray(['id' => '1', 'client_id' => '1']);

        self::assertSame([], $confirmation->items);
        self::assertSame([], $confirmation->taxes);
        self::assertNull($confirmation->status);
    }

    #[Test]
    public function toArrayRoundTrips(): void
    {
        $array = Confirmation::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'CLEARED',
            'currency_code' => 'EUR',
            'offer_id' => '99',
        ])->toArray();

        self::assertSame('CLEARED', $array['status']);
        self::assertSame(99, $array['offer_id']);
        self::assertSame('EUR', $array['currency_code']);
    }
}
