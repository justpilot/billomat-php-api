<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Model\TaxRate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxRate::class)]
final class TaxRateTest extends TestCase
{
    #[Test]
    public function itHydratesAllFieldsFromArray(): void
    {
        $tax = TaxRate::fromArray([
            'id' => '12',
            'account_id' => '99',
            'name' => 'MwSt',
            'rate' => '19.0',
            'is_default' => '1',
        ]);

        self::assertSame(12, $tax->id);
        self::assertSame(99, $tax->accountId);
        self::assertSame('MwSt', $tax->name);
        self::assertSame(19.0, $tax->rate);
        self::assertTrue($tax->isDefault);
    }

    #[Test]
    public function itTreatsIsDefaultZeroAsFalse(): void
    {
        $tax = TaxRate::fromArray([
            'id' => 1,
            'name' => 'Reduced',
            'rate' => 7,
            'is_default' => '0',
        ]);

        self::assertFalse($tax->isDefault);
    }

    #[Test]
    public function itDefaultsMissingFields(): void
    {
        $tax = TaxRate::fromArray([]);

        self::assertNull($tax->id);
        self::assertNull($tax->accountId);
        self::assertSame('', $tax->name);
        self::assertSame(0.0, $tax->rate);
        self::assertFalse($tax->isDefault);
    }

    #[Test]
    public function toArrayMapsCamelCaseBackToSnakeCase(): void
    {
        $tax = TaxRate::fromArray([
            'id' => 12,
            'account_id' => 99,
            'name' => 'MwSt',
            'rate' => 19,
            'is_default' => 1,
        ]);

        self::assertSame(
            [
                'id' => 12,
                'account_id' => 99,
                'name' => 'MwSt',
                'rate' => 19.0,
                'is_default' => 1,
            ],
            $tax->toArray()
        );
    }
}
