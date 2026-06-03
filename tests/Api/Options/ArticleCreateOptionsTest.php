<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Model\Enum\ArticleType;
use Justpilot\Billomat\Model\Enum\NetGross;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArticleCreateOptions::class)]
final class ArticleCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesTypeAndPurchasePriceNetGrossAsApiStrings(): void
    {
        $options = new ArticleCreateOptions(title: 'Beratung');
        $options->type = ArticleType::SERVICE;
        $options->purchasePriceNetGross = NetGross::NET;

        $payload = $options->toArray();

        self::assertSame('SERVICE', $payload['type']);
        self::assertSame('NET', $payload['purchase_price_net_gross']);
    }

    #[Test]
    public function itOmitsTypeAndPurchasePriceNetGrossWhenNull(): void
    {
        $options = new ArticleCreateOptions(title: 'Beratung');

        $payload = $options->toArray();

        self::assertArrayNotHasKey('type', $payload);
        self::assertArrayNotHasKey('purchase_price_net_gross', $payload);
    }
}
