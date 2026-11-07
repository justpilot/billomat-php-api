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
    /** Vollständige Steuerzeile im Format von Billomat (vgl. tests/Fixtures/Contract/invoices/get.json). */
    private const array ROW = [
        'name' => 'MwSt',
        'rate' => '19',
        'amount' => '6.39',
        'amount_plain' => '6.3866',
        'amount_rounded' => '6.39',
        'amount_net' => '33.6134',
        'amount_net_plain' => '33.6134',
        'amount_net_rounded' => '33.61',
        'amount_gross' => '40',
        'amount_gross_plain' => '40.0000',
        'amount_gross_rounded' => '40.00',
    ];

    #[Test]
    public function castsBillomatStrings(): void
    {
        $tax = TaxSummary::fromArray(self::ROW);

        self::assertSame('MwSt', $tax->name);
        self::assertSame(19.0, $tax->rate);
        self::assertSame('6.39', $tax->amount->toString());
        self::assertSame('6.3866', $tax->amountPlain?->toString());
        self::assertSame('6.39', $tax->amountRounded?->toString());
        self::assertSame('33.6134', $tax->amountNet?->toString());
        self::assertSame('33.6134', $tax->amountNetPlain?->toString());
        self::assertSame('33.61', $tax->amountNetRounded?->toString());
        self::assertSame('40', $tax->amountGross?->toString());
        self::assertSame('40', $tax->amountGrossPlain?->toString());
        self::assertSame('40', $tax->amountGrossRounded?->toString());
        self::assertSame([], $tax->extra);
    }

    #[Test]
    public function exportsAllFieldsWithBillomatNames(): void
    {
        self::assertSame([
            'name' => 'MwSt',
            'rate' => 19.0,
            'amount' => '6.39',
            'amount_plain' => '6.3866',
            'amount_rounded' => '6.39',
            'amount_net' => '33.6134',
            'amount_net_plain' => '33.6134',
            'amount_net_rounded' => '33.61',
            'amount_gross' => '40',
            'amount_gross_plain' => '40',
            'amount_gross_rounded' => '40',
        ], TaxSummary::fromArray(self::ROW)->toArray());
    }

    #[Test]
    public function keepsUnknownFieldsInExtra(): void
    {
        $tax = TaxSummary::fromArray([...self::ROW, 'amount_reduced' => '1.00']);

        self::assertSame(['amount_reduced' => '1.00'], $tax->extra);
        self::assertSame('1.00', $tax->toArray()['amount_reduced']);
        self::assertSame('6.39', $tax->toArray()['amount']);
    }

    #[Test]
    public function missingOrInvalidFieldsFallBackToEmptyValues(): void
    {
        $tax = TaxSummary::fromArray(['name' => 7, 'amount' => '']);

        self::assertSame('7', $tax->name);
        self::assertSame(0.0, $tax->rate);
        self::assertTrue($tax->amount->isZero());
        self::assertNull($tax->amountNet);
        self::assertNull($tax->amountGrossRounded);
        self::assertSame([
            'name' => '',
            'rate' => 0.0,
            'amount' => '0',
            'amount_plain' => null,
            'amount_rounded' => null,
            'amount_net' => null,
            'amount_net_plain' => null,
            'amount_net_rounded' => null,
            'amount_gross' => null,
            'amount_gross_plain' => null,
            'amount_gross_rounded' => null,
        ], TaxSummary::fromArray([])->toArray());
    }

    #[Test]
    public function aSingleTaxRowIsNormalizedToAList(): void
    {
        // Billomat liefert eine einzelne Steuerzeile als Objekt statt als Liste.
        $taxes = ListCaster::toList(['name' => 'MwSt', 'rate' => '19', 'amount' => '19'], 'name', TaxSummary::fromArray(...));

        self::assertEquals([new TaxSummary('MwSt', 19.0, Money::of('19'))], $taxes);
    }
}
