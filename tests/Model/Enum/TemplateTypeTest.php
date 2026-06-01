<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\TemplateType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateType::class)]
final class TemplateTypeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCases(): void
    {
        self::assertSame(
            ['DEFINED', 'UPLOADED'],
            array_map(static fn (TemplateType $c): string => $c->value, TemplateType::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(TemplateType::fromApi(null));
        self::assertNull(TemplateType::fromApi('BOGUS'));
        self::assertSame(TemplateType::UPLOADED, TemplateType::fromApi('UPLOADED'));
    }
}
