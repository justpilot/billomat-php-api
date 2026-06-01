<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\TaxRateCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxRateCreateOptions::class)]
final class TaxRateCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesAllFieldsAndIsDefaultAsInt(): void
    {
        $options = new TaxRateCreateOptions(name: 'MwSt', rate: 19.0, isDefault: true);

        self::assertSame(
            ['name' => 'MwSt', 'rate' => 19.0, 'is_default' => 1],
            $options->toArray()
        );
    }

    #[Test]
    public function itDefaultsIsDefaultToZero(): void
    {
        $options = new TaxRateCreateOptions(name: 'Reduced', rate: 7.0);

        self::assertSame(
            ['name' => 'Reduced', 'rate' => 7.0, 'is_default' => 0],
            $options->toArray()
        );
    }
}
