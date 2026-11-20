<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\TemplateUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateUpdateOptions::class)]
final class TemplateUpdateOptionsTest extends TestCase
{
    #[Test]
    public function emptyOptionsProduceEmptyPayload(): void
    {
        self::assertSame([], new TemplateUpdateOptions()->toArray());
    }

    #[Test]
    public function itSerializesNameAndIsDefault(): void
    {
        $options = new TemplateUpdateOptions(name: 'Custom', isDefault: true);

        self::assertSame(['name' => 'Custom', 'is_default' => 1], $options->toArray());

        $options = $options->withIsDefault(false);
        self::assertSame(['name' => 'Custom', 'is_default' => 0], $options->toArray());
    }
}
