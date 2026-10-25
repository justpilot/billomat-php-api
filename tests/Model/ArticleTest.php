<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Article;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Article::class)]
#[UsesClass(ItemType::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class ArticleTest extends TestCase
{
    #[Test]
    public function itHydratesFullArticleFromArray(): void
    {
        $article = Article::fromArray([
            'id' => '7',
            'created' => '2024-01-15T09:00:00+01:00',
            'article_number' => 'A-007',
            'number' => '7',
            'number_pre' => 'A-',
            'number_length' => '3',
            'title' => 'SaaS-Lizenz Pro',
            'description' => 'Jahreslizenz',
            'sales_price' => '199.0',
            'currency_code' => 'EUR',
            'sales_price2' => '189.0',
            'sales_price3' => '179.0',
            'sales_price4' => '169.0',
            'sales_price5' => '159.0',
            'unit' => 'Stück',
            'unit_id' => '42',
            'purchase_price' => '50.0',
            'purchase_price_currency_code' => 'EUR',
            'supplier_id' => '11',
            'tax_id' => '1',
            'category_id' => '5',
        ]);

        self::assertSame(7, $article->id);
        self::assertInstanceOf(DateTimeImmutable::class, $article->created);
        self::assertSame('A-007', $article->articleNumber);
        self::assertSame('SaaS-Lizenz Pro', $article->title);
        self::assertSame('199', $article->salesPrice?->toString());
        self::assertSame('189', $article->salesPrice2?->toString());
        self::assertSame('179', $article->salesPrice3?->toString());
        self::assertSame('169', $article->salesPrice4?->toString());
        self::assertSame('159', $article->salesPrice5?->toString());
        self::assertSame(42, $article->unitId);
        self::assertSame('50', $article->purchasePrice?->toString());
        self::assertSame(11, $article->supplierId);
        self::assertSame(1, $article->taxId);
    }

    #[Test]
    public function itFiltersEmptyStringsAsNullForNumericFields(): void
    {
        $article = Article::fromArray([
            'id' => '1',
            'title' => 'Min',
            'sales_price' => '',
            'sales_price2' => '',
            'purchase_price' => '',
            'unit_id' => '',
            'supplier_id' => '',
            'tax_id' => '',
            'category_id' => '',
            'number_length' => '',
        ]);

        self::assertNull($article->salesPrice);
        self::assertNull($article->salesPrice2);
        self::assertNull($article->purchasePrice);
        self::assertNull($article->unitId);
        self::assertNull($article->supplierId);
        self::assertNull($article->taxId);
        self::assertNull($article->numberLength);
    }

    #[Test]
    public function itHandlesInvalidCreatedDateGracefully(): void
    {
        $article = Article::fromArray(['id' => '1', 'created' => 'not-a-date']);

        self::assertNull($article->created);
    }

    #[Test]
    public function toArrayRoundTrips(): void
    {
        $array = Article::fromArray([
            'id' => '7',
            'title' => 'Test',
            'sales_price' => '99.99',
            'currency_code' => 'EUR',
            'unit' => 'Stück',
        ])->toArray();

        self::assertSame('99.99', $array['sales_price']);
        self::assertSame('EUR', $array['currency_code']);
        self::assertArrayNotHasKey('unit', $array);
    }
}
