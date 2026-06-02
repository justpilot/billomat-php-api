<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceItemType::class)]
final class InvoiceItemTypeTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCasesAndApiValues(): void
    {
        self::assertSame('PRODUCT', InvoiceItemType::PRODUCT->value);
        self::assertSame('SERVICE', InvoiceItemType::SERVICE->value);
    }

    #[Test]
    public function fromApiReturnsNullForNullOrUnknown(): void
    {
        self::assertNull(InvoiceItemType::fromApi(null));
        self::assertNull(InvoiceItemType::fromApi('BOGUS'));
        self::assertSame(InvoiceItemType::PRODUCT, InvoiceItemType::fromApi('PRODUCT'));
    }
}
