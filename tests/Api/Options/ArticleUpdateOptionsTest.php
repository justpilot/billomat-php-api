<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\Model\Enum\ArticleType;
use Justpilot\Billomat\Model\Enum\NetGross;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArticleUpdateOptions::class)]
final class ArticleUpdateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesTypeAndPurchasePriceNetGross(): void
    {
        $options = new ArticleUpdateOptions();
        $options->type = ArticleType::PRODUCT;
        $options->purchasePriceNetGross = NetGross::GROSS;

        $payload = $options->toArray();

        self::assertSame('PRODUCT', $payload['type']);
        self::assertSame('GROSS', $payload['purchase_price_net_gross']);
    }

    #[Test]
    public function defaultPayloadIsEmpty(): void
    {
        self::assertSame([], new ArticleUpdateOptions()->toArray());
    }
}
