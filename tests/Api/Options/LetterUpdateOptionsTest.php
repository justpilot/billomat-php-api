<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\LetterUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LetterUpdateOptions::class)]
final class LetterUpdateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesSupplierId(): void
    {
        $options = new LetterUpdateOptions();
        $options->supplierId = 99;

        self::assertSame(99, $options->toArray()['supplier_id']);
    }
}
