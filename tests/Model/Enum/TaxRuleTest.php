<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\TaxRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxRule::class)]
final class TaxRuleTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCases(): void
    {
        self::assertSame(
            ['TAX', 'NO_TAX', 'COUNTRY'],
            array_map(static fn (TaxRule $c): string => $c->value, TaxRule::cases())
        );
    }
}
