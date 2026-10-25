<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\TaxSummary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxSummary::class)]
#[UsesClass(ListCaster::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class TaxSummaryTest extends TestCase
{
    #[Test]
    public function castsBillomatStrings(): void
    {
        $tax = TaxSummary::fromArray(['name' => 'MwSt', 'rate' => '19', 'amount' => '1.90']);

        self::assertSame('MwSt', $tax->name);
        self::assertSame(19.0, $tax->rate);
        self::assertSame('1.9', $tax->amount->toString());
        self::assertSame(['name' => 'MwSt', 'rate' => 19.0, 'amount' => '1.9'], $tax->toArray());
    }

    #[Test]
    public function missingOrInvalidFieldsFallBackToEmptyValues(): void
    {
        $tax = TaxSummary::fromArray(['name' => 7, 'amount' => '']);

        self::assertSame('7', $tax->name);
        self::assertSame(0.0, $tax->rate);
        self::assertTrue($tax->amount->isZero());
        self::assertSame(['name' => '', 'rate' => 0.0, 'amount' => '0'], TaxSummary::fromArray([])->toArray());
    }

    #[Test]
    public function aSingleTaxRowIsNormalizedToAList(): void
    {
        // Billomat liefert eine einzelne Steuerzeile als Objekt statt als Liste.
        $taxes = ListCaster::toList(['name' => 'MwSt', 'rate' => '19', 'amount' => '19'], 'name', TaxSummary::fromArray(...));

        self::assertEquals([new TaxSummary('MwSt', 19.0, Money::of('19'))], $taxes);
    }
}
