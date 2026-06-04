<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\OfferStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferStatus::class)]
final class OfferStatusTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        self::assertSame(
            ['DRAFT', 'OPEN', 'ACCEPTED', 'REJECTED', 'CLEARED', 'CANCELED'],
            array_map(static fn (OfferStatus $c): string => $c->value, OfferStatus::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(OfferStatus::fromApi(null));
        self::assertNull(OfferStatus::fromApi('BOGUS'));
        self::assertSame(OfferStatus::ACCEPTED, OfferStatus::fromApi('ACCEPTED'));
        self::assertSame(OfferStatus::REJECTED, OfferStatus::fromApi('REJECTED'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (OfferStatus::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
