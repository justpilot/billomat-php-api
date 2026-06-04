<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNoteItem;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeliveryNote::class)]
final class DeliveryNoteTest extends TestCase
{
    #[Test]
    public function itHydratesFullDeliveryNoteFromArray(): void
    {
        $note = DeliveryNote::fromArray([
            'id' => '33',
            'client_id' => '42',
            'invoice_id' => '100',
            'confirmation_id' => '12',
            'delivery_note_number' => 'LS-2025-0033',
            'status' => 'OPEN',
            'date' => '2025-06-01',
            'currency_code' => 'EUR',
            'net_gross' => 'NET',
            'taxes' => [
                'tax' => [
                    ['name' => 'MwSt', 'rate' => '19.0', 'amount' => '19.0'],
                ],
            ],
            'delivery-note-items' => [
                'delivery-note-item' => [
                    [
                        'id' => '1',
                        'delivery_note_id' => '33',
                        'quantity' => '3',
                        'unit_price' => '20',
                        'title' => 'Buch',
                    ],
                ],
            ],
        ]);

        self::assertSame(33, $note->id);
        self::assertSame(42, $note->clientId);
        self::assertSame(100, $note->invoiceId);
        self::assertSame(12, $note->confirmationId);
        self::assertSame(DeliveryNoteStatus::OPEN, $note->status);
        self::assertInstanceOf(DateTimeImmutable::class, $note->date);
        self::assertSame(NetGross::NET, $note->netGross);

        self::assertCount(1, $note->items);
        self::assertContainsOnlyInstancesOf(DeliveryNoteItem::class, $note->items);
        self::assertSame(3.0, $note->items[0]->quantity);
    }

    #[Test]
    public function itHandlesSingleItemArrayShape(): void
    {
        $note = DeliveryNote::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'DRAFT',
            'delivery-note-items' => [
                'delivery-note-item' => [
                    'id' => '1',
                    'delivery_note_id' => '1',
                    'quantity' => '1',
                    'unit_price' => '50',
                ],
            ],
        ]);

        self::assertCount(1, $note->items);
    }

    #[Test]
    public function itDefaultsToEmptyItemsWhenAbsent(): void
    {
        $note = DeliveryNote::fromArray(['id' => '1', 'client_id' => '1']);

        self::assertSame([], $note->items);
        self::assertSame([], $note->taxes);
        self::assertNull($note->status);
    }

    #[Test]
    public function toArrayRoundTrips(): void
    {
        $array = DeliveryNote::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'CLEARED',
            'confirmation_id' => '5',
        ])->toArray();

        self::assertSame('CLEARED', $array['status']);
        self::assertSame(5, $array['confirmation_id']);
    }
}
