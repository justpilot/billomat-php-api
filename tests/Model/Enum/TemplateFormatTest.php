<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\TemplateFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateFormat::class)]
final class TemplateFormatTest extends TestCase
{
    #[Test]
    public function itExposesExpectedLowercaseValues(): void
    {
        // Billomat erwartet kleinbuchstaben.
        self::assertSame(
            ['doc', 'docx', 'rtf'],
            array_map(static fn (TemplateFormat $c): string => $c->value, TemplateFormat::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(TemplateFormat::fromApi(null));
        self::assertNull(TemplateFormat::fromApi('xls'));
        self::assertSame(TemplateFormat::DOCX, TemplateFormat::fromApi('docx'));
    }
}
