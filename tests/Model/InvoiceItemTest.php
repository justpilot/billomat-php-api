<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\InvoiceItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceItem::class)]
final class InvoiceItemTest extends TestCase
{
    #[Test]
    public function itHydratesAllFieldsFromArray(): void
    {
        $item = InvoiceItem::fromArray([
            'id' => '1001',
            'invoice_id' => '240',
            'article_id' => '7',
            'position' => '2',
            'unit' => 'Stück',
            'quantity' => '3.5',
            'unit_price' => '49.90',
            'tax_name' => 'MwSt',
            'tax_rate' => '19',
            'tax_changed_manually' => '1',
            'title' => 'Beratung',
            'description' => 'Workshop',
            'reduction' => '10%',
            'type' => 'SERVICE',
            'total_gross' => '208.05',
            'total_net' => '174.83',
            'total_gross_unreduced' => '231.17',
            'total_net_unreduced' => '194.25',
            'created' => '2024-05-01T09:00:00+02:00',
        ]);

        self::assertSame(1001, $item->id);
        self::assertSame(240, $item->invoiceId);
        self::assertSame(7, $item->articleId);
        self::assertSame(2, $item->position);
        self::assertSame('Stück', $item->unit);
        self::assertSame(3.5, $item->quantity);
        self::assertSame(49.90, $item->unitPrice);
        self::assertSame('MwSt', $item->taxName);
        self::assertSame(19.0, $item->taxRate);
        self::assertTrue($item->taxChangedManually);
        self::assertSame('10%', $item->reduction);
        self::assertSame(InvoiceItemType::SERVICE, $item->type);
        self::assertSame(208.05, $item->totalGross);
        self::assertSame(174.83, $item->totalNet);
        self::assertInstanceOf(DateTimeImmutable::class, $item->created);
    }

    #[Test]
    public function itDefaultsRequiredQuantityAndPriceToZero(): void
    {
        $item = InvoiceItem::fromArray([]);

        self::assertSame(0.0, $item->quantity);
        self::assertSame(0.0, $item->unitPrice);
    }

    #[Test]
    public function itTreatsEmptyArticleIdAsNull(): void
    {
        // article_id ist optional und wird teils als "" geliefert
        $item = InvoiceItem::fromArray([
            'quantity' => 1,
            'unit_price' => 10,
            'article_id' => '',
        ]);

        self::assertNull($item->articleId);
    }

    #[Test]
    public function itResolvesUnknownTypeToNull(): void
    {
        // Billomat-Doku lässt zwei Werte zu (SERVICE/PRODUCT); andere Eingaben dürfen nicht aufgespielt werden
        $item = InvoiceItem::fromArray([
            'quantity' => 1,
            'unit_price' => 1,
            'type' => 'WHATEVER',
        ]);

        self::assertNull($item->type);
    }

    #[Test]
    public function itHandlesInvalidCreatedGracefully(): void
    {
        $item = InvoiceItem::fromArray([
            'quantity' => 1,
            'unit_price' => 1,
            'created' => 'invalid',
        ]);

        self::assertNull($item->created);
    }

    #[Test]
    public function toArraySerializesEnumValue(): void
    {
        $item = InvoiceItem::fromArray([
            'quantity' => 1,
            'unit_price' => 1,
            'type' => 'PRODUCT',
        ]);

        $array = $item->toArray();
        self::assertSame('PRODUCT', $array['type']);
    }
}
