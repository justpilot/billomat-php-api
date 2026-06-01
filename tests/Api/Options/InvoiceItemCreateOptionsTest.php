<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceItemCreateOptions::class)]
final class InvoiceItemCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadAlwaysKeepsQuantityAndUnitPrice(): void
    {
        // quantity/unit_price dürfen nie ausgefiltert werden — selbst bei 0
        $options = new InvoiceItemCreateOptions(quantity: 0.0, unitPrice: 0.0);

        $payload = $options->toArray();

        self::assertSame(0.0, $payload['quantity']);
        self::assertSame(0.0, $payload['unit_price']);
        self::assertArrayNotHasKey('type', $payload);
        self::assertArrayNotHasKey('article_id', $payload);
    }

    #[Test]
    public function itSerializesTypeEnumAsStringValue(): void
    {
        $options = new InvoiceItemCreateOptions(quantity: 1.0, unitPrice: 10.0);
        $options->type = InvoiceItemType::PRODUCT;

        $payload = $options->toArray();

        self::assertSame('PRODUCT', $payload['type']);
    }

    #[Test]
    public function itStripsAllOtherNullFields(): void
    {
        $options = new InvoiceItemCreateOptions(quantity: 2.0, unitPrice: 5.5);
        $options->title = 'Beratung';
        $options->position = 3;

        $payload = $options->toArray();

        self::assertSame(
            ['title' => 'Beratung', 'quantity' => 2.0, 'unit_price' => 5.5, 'position' => 3],
            $payload
        );
    }

    #[Test]
    public function itSerializesAllOptionalFields(): void
    {
        $options = new InvoiceItemCreateOptions(quantity: 1.0, unitPrice: 99.0);
        $options->type = InvoiceItemType::SERVICE;
        $options->articleId = 7;
        $options->title = 'Workshop';
        $options->description = 'Tagesworkshop';
        $options->unit = 'Stunde';
        $options->taxName = 'MwSt';
        $options->taxRate = 19.0;
        $options->taxChangedManually = true;
        $options->reduction = '10%';
        $options->position = 1;

        $payload = $options->toArray();

        self::assertSame('SERVICE', $payload['type']);
        self::assertSame(7, $payload['article_id']);
        self::assertSame('Workshop', $payload['title']);
        self::assertSame('Tagesworkshop', $payload['description']);
        self::assertSame('Stunde', $payload['unit']);
        self::assertSame('MwSt', $payload['tax_name']);
        self::assertSame(19.0, $payload['tax_rate']);
        self::assertTrue($payload['tax_changed_manually']);
        self::assertSame('10%', $payload['reduction']);
        self::assertSame(1, $payload['position']);
    }
}
