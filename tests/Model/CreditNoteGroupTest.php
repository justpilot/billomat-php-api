<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\CreditNoteGroup;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreditNoteGroup::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
#[UsesClass(CreditNoteStatus::class)]
final class CreditNoteGroupTest extends TestCase
{
    #[Test]
    public function itHydratesAGroupAndKeepsUnknownFields(): void
    {
        // Gekürzte Antwort von GET /credit-notes?group_by=status (Aufbau wie in der Sandbox).
        $group = CreditNoteGroup::fromArray([
            'total_gross' => '408',
            'total_net' => '342.86',
            'status' => 'PAID',
            'credit-note-params' => ['status' => 'PAID', 7 => 'x', 'nested' => ['nein']],
            'count' => '3',
        ]);

        self::assertSame('408', $group->totalGross?->toString());
        self::assertSame(CreditNoteStatus::PAID, $group->status);
        self::assertSame(['status' => 'PAID', '7' => 'x'], $group->creditNoteParams);
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
            'credit-note-params' => ['status' => 'PAID', '7' => 'x'],
        ], $group->toArray());
    }

    #[Test]
    public function itIgnoresParamsThatAreNoObject(): void
    {
        $group = CreditNoteGroup::fromArray(['total_gross' => '1', 'total_net' => '1', 'year' => '', 'credit-note-params' => 'kaputt']);

        self::assertSame([], $group->creditNoteParams);
        self::assertSame([], $group->extra);
        // Die Gruppe ohne Datum kommt mit leerem Zeitfeld.
        self::assertNull($group->year);
    }
}
