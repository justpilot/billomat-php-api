<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\Clear;
use Justpilot\Billomat\Api\PropertyUpdateOptions;
use Justpilot\Billomat\Api\TaxRateUpdateOptions;
use Justpilot\Billomat\Model\Enum\PropertyType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Teil-Updates für Steuersätze und Attribut-Definitionen: nur gesetzte Felder
 * gehen raus, Bool-Felder als 1/0, {@see Clear::Field} als null.
 */
#[CoversClass(TaxRateUpdateOptions::class)]
#[CoversClass(PropertyUpdateOptions::class)]
final class PartialUpdateOptionsTest extends TestCase
{
    #[Test]
    public function anEmptyTaxRateUpdateSendsNothing(): void
    {
        self::assertSame([], new TaxRateUpdateOptions()->toArray());
    }

    #[Test]
    public function taxRateUpdateSendsBooleansAsOneOrZero(): void
    {
        self::assertSame(['is_default' => 1], new TaxRateUpdateOptions(isDefault: true)->toArray());
        self::assertSame(['is_default' => 0], new TaxRateUpdateOptions(isDefault: false)->toArray());
    }

    #[Test]
    public function taxRateUpdateSendsOnlyTheSetFields(): void
    {
        self::assertSame(['rate' => 7.0], new TaxRateUpdateOptions(rate: 7.0)->toArray());
    }

    #[Test]
    public function anEmptyPropertyUpdateSendsNothing(): void
    {
        self::assertSame([], new PropertyUpdateOptions()->toArray());
    }

    #[Test]
    public function propertyUpdateSendsBooleansAsOneOrZero(): void
    {
        self::assertSame(['is_nvl' => 1], new PropertyUpdateOptions(isNvl: true)->toArray());
        self::assertSame(['is_nvl' => 0], new PropertyUpdateOptions(isNvl: false)->toArray());
    }

    #[Test]
    public function propertyUpdateClearsAField(): void
    {
        self::assertSame(
            ['type' => 'CHECKBOX', 'default_value' => null],
            new PropertyUpdateOptions(type: PropertyType::CHECKBOX, defaultValue: Clear::Field)->toArray(),
        );
    }
}
