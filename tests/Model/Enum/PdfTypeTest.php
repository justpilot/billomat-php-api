<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\PdfType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PdfType::class)]
final class PdfTypeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedLowercaseApiValues(): void
    {
        // Billomat erwartet hier kleinbuchstaben: ?type=signed
        self::assertSame('signed', PdfType::SIGNED->value);
        self::assertSame('print', PdfType::PRINT->value);
    }

    #[Test]
    public function fromApiHandlesNullEmptyAndUnknown(): void
    {
        self::assertNull(PdfType::fromApi(null));
        self::assertNull(PdfType::fromApi(''));
        self::assertNull(PdfType::fromApi('BOGUS'));
        self::assertSame(PdfType::SIGNED, PdfType::fromApi('signed'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (PdfType::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
