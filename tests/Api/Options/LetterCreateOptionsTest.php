<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\LetterCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LetterCreateOptions::class)]
final class LetterCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesSupplierId(): void
    {
        $options = new LetterCreateOptions(clientId: 42);
        $options->supplierId = 99;

        self::assertSame(99, $options->toArray()['supplier_id']);
    }

    #[Test]
    public function itOmitsSupplierIdWhenNull(): void
    {
        $options = new LetterCreateOptions(clientId: 42);

        self::assertArrayNotHasKey('supplier_id', $options->toArray());
    }
}
