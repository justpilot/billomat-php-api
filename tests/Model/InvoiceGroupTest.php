<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\InvoiceGroup;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceGroup::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
#[UsesClass(InvoiceStatus::class)]
final class InvoiceGroupTest extends TestCase
{
    #[Test]
    public function itHydratesAGroupAndKeepsUnknownFields(): void
    {
        // Gekürzte Antwort von GET /invoices?group_by=status aus der Sandbox.
        $group = InvoiceGroup::fromArray([
            'total_gross' => '408',
            'total_net' => '342.86',
            'status' => 'PAID',
            'invoice-params' => ['status' => 'PAID', 7 => 'x', 'nested' => ['nein']],
            'count' => '3',
        ]);

        self::assertSame('408', $group->totalGross?->toString());
        self::assertSame(InvoiceStatus::PAID, $group->status);
        self::assertSame(['status' => 'PAID', '7' => 'x'], $group->invoiceParams);
        self::assertSame(['count' => '3'], $group->extra);

        self::assertSame([
            'count' => '3',
            'total_gross' => '408',
            'total_net' => '342.86',
            'client_id' => null,
            'status' => 'PAID',
            'day' => null,
            'week' => null,
            'month' => null,
            'year' => null,
            'invoice-params' => ['status' => 'PAID', '7' => 'x'],
        ], $group->toArray());
    }

    #[Test]
    public function itIgnoresInvoiceParamsThatAreNoObject(): void
    {
        $group = InvoiceGroup::fromArray(['total_gross' => '1', 'total_net' => '1', 'year' => '', 'invoice-params' => 'kaputt']);

        self::assertSame([], $group->invoiceParams);
        self::assertSame([], $group->extra);
        // Die Gruppe ohne Datum kommt mit leerem Zeitfeld.
        self::assertNull($group->year);
    }
}
