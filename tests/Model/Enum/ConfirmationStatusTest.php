<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfirmationStatus::class)]
final class ConfirmationStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        // Billomat-API-Werte — Drift = Regression.
        self::assertSame(
            ['DRAFT', 'OPEN', 'CLEARED', 'CANCELED'],
            array_map(static fn (ConfirmationStatus $c): string => $c->value, ConfirmationStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(ConfirmationStatus::fromApi(null));
        self::assertNull(ConfirmationStatus::fromApi('BOGUS'));
        self::assertSame(ConfirmationStatus::OPEN, ConfirmationStatus::fromApi('OPEN'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (ConfirmationStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
