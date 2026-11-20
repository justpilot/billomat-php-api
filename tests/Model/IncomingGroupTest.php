<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Justpilot\Billomat\Model\IncomingGroup;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncomingGroup::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
#[UsesClass(IncomingStatus::class)]
final class IncomingGroupTest extends TestCase
{
    #[Test]
    public function itHydratesAGroupAndKeepsUnknownFields(): void
    {
        // Gekürzte Antwort von GET /incomings?group_by=status (Aufbau wie in der Sandbox).
        $group = IncomingGroup::fromArray([
            'total_gross' => '408',
            'total_net' => '342.86',
            'status' => 'PAID',
            'incoming-params' => ['status' => 'PAID', 7 => 'x', 'nested' => ['nein']],
            'count' => '3',
        ]);

        self::assertSame('408', $group->totalGross?->toString());
        self::assertSame(IncomingStatus::PAID, $group->status);
        self::assertSame(['status' => 'PAID', '7' => 'x'], $group->incomingParams);
        self::assertSame(['count' => '3'], $group->extra);

        self::assertSame([
            'count' => '3',
            'total_gross' => '408',
            'total_net' => '342.86',
            'supplier_id' => null,
            'status' => 'PAID',
            'day' => null,
            'week' => null,
            'month' => null,
            'year' => null,
            'incoming-params' => ['status' => 'PAID', '7' => 'x'],
        ], $group->toArray());
    }

    #[Test]
    public function itIgnoresParamsThatAreNoObject(): void
    {
        $group = IncomingGroup::fromArray(['total_gross' => '1', 'total_net' => '1', 'year' => '', 'incoming-params' => 'kaputt']);

        self::assertSame([], $group->incomingParams);
        self::assertSame([], $group->extra);
        // Die Gruppe ohne Datum kommt mit leerem Zeitfeld.
        self::assertNull($group->year);
    }
}
