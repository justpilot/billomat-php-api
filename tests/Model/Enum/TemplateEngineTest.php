<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\TemplateEngine;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateEngine::class)]
final class TemplateEngineTest extends TestCase
{
    #[Test]
    public function itExposesDefaultCase(): void
    {
        self::assertSame('DEFAULT', TemplateEngine::DEFAULT->value);
        self::assertSame([TemplateEngine::DEFAULT], TemplateEngine::cases());
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(TemplateEngine::fromApi(null));
        self::assertNull(TemplateEngine::fromApi('BOGUS'));
        self::assertSame(TemplateEngine::DEFAULT, TemplateEngine::fromApi('DEFAULT'));
    }
}
