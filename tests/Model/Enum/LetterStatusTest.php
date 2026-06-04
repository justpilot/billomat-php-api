<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\LetterStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LetterStatus::class)]
final class LetterStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        self::assertSame(
            ['DRAFT', 'OPEN', 'CLEARED', 'CANCELED'],
            array_map(static fn (LetterStatus $c): string => $c->value, LetterStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(LetterStatus::fromApi(null));
        self::assertNull(LetterStatus::fromApi('BOGUS'));
        self::assertSame(LetterStatus::CLEARED, LetterStatus::fromApi('CLEARED'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (LetterStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
