<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\ItemType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ItemType::class)]
final class ItemTypeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCasesAndApiValues(): void
    {
        self::assertSame('PRODUCT', ItemType::PRODUCT->value);
        self::assertSame('SERVICE', ItemType::SERVICE->value);
    }

    #[Test]
    public function fromApiReturnsNullForNullOrUnknown(): void
    {
        self::assertNull(ItemType::fromApi(null));
        self::assertNull(ItemType::fromApi('BOGUS'));
        self::assertSame(ItemType::PRODUCT, ItemType::fromApi('PRODUCT'));
    }
}
