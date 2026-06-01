<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceStatus::class)]
final class InvoiceStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        // Billomat-API-Werte — Drift = Regression.
        self::assertSame(
            ['DRAFT', 'OPEN', 'OVERDUE', 'PAID', 'CANCELED'],
            array_map(static fn (InvoiceStatus $c): string => $c->value, InvoiceStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(InvoiceStatus::fromApi(null));
        self::assertNull(InvoiceStatus::fromApi('BOGUS'));
        self::assertSame(InvoiceStatus::OPEN, InvoiceStatus::fromApi('OPEN'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (InvoiceStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
