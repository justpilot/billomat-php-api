<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateThumbFormat::class)]
final class TemplateThumbFormatTest extends TestCase
{
    #[Test]
    public function itExposesExpectedLowercaseValues(): void
    {
        self::assertSame(
            ['png', 'gif', 'jpg'],
            array_map(static fn (TemplateThumbFormat $c): string => $c->value, TemplateThumbFormat::cases())
        );
    }
}
