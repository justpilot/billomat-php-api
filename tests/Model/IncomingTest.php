<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Justpilot\Billomat\Model\Incoming;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Incoming::class)]
final class IncomingTest extends TestCase
{
    #[Test]
    public function itHydratesFullIncomingFromArray(): void
    {
        $incoming = Incoming::fromArray([
            'id' => '50',
            'supplier_id' => '11',
            'created' => '2025-01-15T08:00:00+01:00',
            'date' => '2025-01-10',
            'supply_date' => '2025-01-08',
            'due_date' => '2025-02-10',
            'due_days' => '30',
            'paid_at' => '2025-02-05',
            'status' => 'PAID',
            'incoming_number' => 'ER-2025-0050',
            'address' => "Acme Lieferanten GmbH\nLagerweg 5\n12345 Berlin",
            'label' => 'Büromaterial',
            'intro' => 'Rechnung Nr. 12345',
            'note' => 'Lieferschein-Nr. 999',
            'total_gross' => '119.0',
            'total_net' => '100.0',
            'paid_amount' => '119.0',
            'open_amount' => '0.0',
            'currency_code' => 'EUR',
            'quote' => '1.0',
        ]);

        self::assertSame(50, $incoming->id);
        self::assertSame(11, $incoming->supplierId);
        self::assertInstanceOf(DateTimeImmutable::class, $incoming->created);
        self::assertInstanceOf(DateTimeImmutable::class, $incoming->date);
        self::assertInstanceOf(DateTimeImmutable::class, $incoming->supplyDate);
        self::assertInstanceOf(DateTimeImmutable::class, $incoming->dueDate);
        self::assertInstanceOf(DateTimeImmutable::class, $incoming->paidAt);
        self::assertSame(30, $incoming->dueDays);
        self::assertSame(IncomingStatus::PAID, $incoming->status);
        self::assertSame('ER-2025-0050', $incoming->incomingNumber);
        self::assertSame(119.0, $incoming->totalGross);
        self::assertSame(0.0, $incoming->openAmount);
    }

    #[Test]
    public function itFiltersEmptyStringsAsNullForNumericFields(): void
    {
        $incoming = Incoming::fromArray([
            'id' => '1',
            'supplier_id' => '',
            'due_days' => '',
            'total_gross' => '',
            'total_net' => '',
            'paid_amount' => '',
            'open_amount' => '',
            'quote' => '',
        ]);

        self::assertNull($incoming->supplierId);
        self::assertNull($incoming->dueDays);
        self::assertNull($incoming->totalGross);
        self::assertNull($incoming->totalNet);
        self::assertNull($incoming->paidAmount);
        self::assertNull($incoming->openAmount);
        self::assertNull($incoming->quote);
    }

    #[Test]
    public function itHandlesUnknownStatus(): void
    {
        $incoming = Incoming::fromArray(['id' => '1', 'status' => 'BOGUS']);

        self::assertNull($incoming->status);
    }

    #[Test]
    public function toArrayRoundTrips(): void
    {
        $array = Incoming::fromArray([
            'id' => '1',
            'supplier_id' => '11',
            'status' => 'OVERDUE',
            'date' => '2025-01-10',
            'currency_code' => 'EUR',
        ])->toArray();

        self::assertSame('OVERDUE', $array['status']);
        self::assertSame('2025-01-10', $array['date']);
        self::assertSame(11, $array['supplier_id']);
        self::assertSame('EUR', $array['currency_code']);
    }
}
