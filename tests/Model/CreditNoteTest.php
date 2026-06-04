<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNoteItem;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreditNote::class)]
final class CreditNoteTest extends TestCase
{
    #[Test]
    public function itHydratesFullCreditNoteFromArray(): void
    {
        $note = CreditNote::fromArray([
            'id' => '21',
            'client_id' => '42',
            'invoice_id' => '100',
            'credit_note_number' => 'GS-2025-0021',
            'status' => 'PAID',
            'date' => '2025-05-01',
            'currency_code' => 'EUR',
            'net_gross' => 'NET',
            'total_gross' => '119.0',
            'total_net' => '100.0',
            'paid_amount' => '119.0',
            'open_amount' => '0.0',
            'taxes' => [
                'tax' => [
                    'name' => 'MwSt',
                    'rate' => '19.0',
                    'amount' => '19.0',
                ],
            ],
            'credit-note-items' => [
                'credit-note-item' => [
                    [
                        'id' => '1',
                        'credit_note_id' => '21',
                        'quantity' => '1',
                        'unit_price' => '100',
                        'title' => 'Storno',
                    ],
                ],
            ],
        ]);

        self::assertSame(21, $note->id);
        self::assertSame(42, $note->clientId);
        self::assertSame(100, $note->invoiceId);
        self::assertSame('GS-2025-0021', $note->creditNoteNumber);
        self::assertSame(CreditNoteStatus::PAID, $note->status);
        self::assertInstanceOf(DateTimeImmutable::class, $note->date);
        self::assertSame(NetGross::NET, $note->netGross);
        self::assertSame(119.0, $note->paidAmount);
        self::assertSame(0.0, $note->openAmount);

        self::assertCount(1, $note->items);
        self::assertContainsOnlyInstancesOf(CreditNoteItem::class, $note->items);
    }

    #[Test]
    public function itHandlesSingleItemArrayShape(): void
    {
        $note = CreditNote::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'DRAFT',
            'credit-note-items' => [
                'credit-note-item' => [
                    'id' => '1',
                    'credit_note_id' => '1',
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
        $note = CreditNote::fromArray(['id' => '1', 'client_id' => '1']);

        self::assertSame([], $note->items);
        self::assertSame([], $note->taxes);
        self::assertNull($note->status);
    }

    #[Test]
    public function toArrayRoundTrips(): void
    {
        $array = CreditNote::fromArray([
            'id' => '1',
            'client_id' => '1',
            'status' => 'CANCELED',
            'invoice_id' => '99',
        ])->toArray();

        self::assertSame('CANCELED', $array['status']);
        self::assertSame(99, $array['invoice_id']);
    }
}
