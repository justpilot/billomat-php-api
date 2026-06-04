<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Recurring;
use Justpilot\Billomat\Model\RecurringItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Recurring::class)]
final class RecurringTest extends TestCase
{
    #[Test]
    public function itHydratesFullRecurringFromArray(): void
    {
        $recurring = Recurring::fromArray([
            'id' => '5',
            'client_id' => '42',
            'name' => 'Monatslizenz Acme',
            'currency_code' => 'EUR',
            'net_gross' => 'GROSS',
            'action' => 'COMPLETE',
            'cycle' => 'MONTHLY',
            'cycle_number' => '1',
            'hour' => '8',
            'start_date' => '2025-05-01',
            'end_date' => '2026-04-30',
            'next_creation_date' => '2025-06-01',
            'iterations' => '12',
            'counter' => '2',
            'total_gross' => '119.0',
            'total_net' => '100.0',
            'email_subject' => 'Ihre monatliche Rechnung',
            'taxes' => [
                'tax' => [
                    ['name' => 'MwSt', 'rate' => '19.0', 'amount' => '19.0'],
                ],
            ],
            'recurring-items' => [
                'recurring-item' => [
                    [
                        'id' => '1',
                        'recurring_id' => '5',
                        'quantity' => '1',
                        'unit_price' => '100',
                        'title' => 'SaaS-Lizenz',
                    ],
                ],
            ],
        ]);

        self::assertSame(5, $recurring->id);
        self::assertSame(42, $recurring->clientId);
        self::assertSame(RecurringAction::COMPLETE, $recurring->action);
        self::assertSame(RecurringCycle::MONTHLY, $recurring->cycle);
        self::assertSame(1, $recurring->cycleNumber);
        self::assertSame(8, $recurring->hour);
        self::assertInstanceOf(DateTimeImmutable::class, $recurring->startDate);
        self::assertInstanceOf(DateTimeImmutable::class, $recurring->endDate);
        self::assertInstanceOf(DateTimeImmutable::class, $recurring->nextCreationDate);
        self::assertSame(12, $recurring->iterations);
        self::assertSame(2, $recurring->counter);
        self::assertSame(NetGross::GROSS, $recurring->netGross);

        self::assertCount(1, $recurring->items);
        self::assertContainsOnlyInstancesOf(RecurringItem::class, $recurring->items);
        self::assertCount(1, $recurring->taxes);
    }

    #[Test]
    public function itHandlesUnknownEnumValuesAsNull(): void
    {
        $recurring = Recurring::fromArray([
            'id' => '1',
            'client_id' => '1',
            'action' => 'BOGUS',
            'cycle' => 'INVALID',
        ]);

        self::assertNull($recurring->action);
        self::assertNull($recurring->cycle);
    }

    #[Test]
    public function itHandlesSingleItemArrayShape(): void
    {
        $recurring = Recurring::fromArray([
            'id' => '1',
            'client_id' => '1',
            'cycle' => 'WEEKLY',
            'recurring-items' => [
                'recurring-item' => [
                    'id' => '1',
                    'recurring_id' => '1',
                    'quantity' => '1',
                    'unit_price' => '20',
                ],
            ],
        ]);

        self::assertCount(1, $recurring->items);
        self::assertSame(RecurringCycle::WEEKLY, $recurring->cycle);
    }

    #[Test]
    public function toArraySerialisesEnumsAndItems(): void
    {
        $array = Recurring::fromArray([
            'id' => '5',
            'client_id' => '1',
            'action' => 'EMAIL',
            'cycle' => 'YEARLY',
            'cycle_number' => '1',
            'start_date' => '2025-05-01',
        ])->toArray();

        self::assertSame('EMAIL', $array['action']);
        self::assertSame('YEARLY', $array['cycle']);
        self::assertSame('2025-05-01', $array['start_date']);
        self::assertArrayNotHasKey('recurring-items', $array);
    }
}
