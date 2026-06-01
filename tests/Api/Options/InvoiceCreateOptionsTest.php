<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use DateTimeImmutable;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceCreateOptions::class)]
final class InvoiceCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadOnlyContainsClientId(): void
    {
        $options = new InvoiceCreateOptions(clientId: 42);

        $payload = $options->toArray();

        self::assertSame(['client_id' => 42], $payload);
    }

    #[Test]
    public function nullFieldsAreStrippedFromPayload(): void
    {
        $options = new InvoiceCreateOptions(clientId: 1);

        $payload = $options->toArray();

        self::assertArrayNotHasKey('contact_id', $payload);
        self::assertArrayNotHasKey('address', $payload);
        self::assertArrayNotHasKey('number_pre', $payload);
        self::assertArrayNotHasKey('date', $payload);
        self::assertArrayNotHasKey('invoice-items', $payload);
    }

    #[Test]
    public function itSerializesEnumsAsTheirStringValue(): void
    {
        $options = new InvoiceCreateOptions(clientId: 1);
        $options->netGross = NetGross::NET;
        $options->supplyDateType = SupplyDateType::SUPPLY_DATE;

        $payload = $options->toArray();

        self::assertSame('NET', $payload['net_gross']);
        self::assertSame('SUPPLY_DATE', $payload['supply_date_type']);
    }

    #[Test]
    public function itFormatsDatesAsIsoLocalDate(): void
    {
        $options = new InvoiceCreateOptions(clientId: 1);
        $options->date = new DateTimeImmutable('2024-05-30T15:42:00');
        $options->supplyDate = new DateTimeImmutable('2024-05-25');
        $options->dueDate = new DateTimeImmutable('2024-06-14');
        $options->discountDate = new DateTimeImmutable('2024-06-07');

        $payload = $options->toArray();

        self::assertSame('2024-05-30', $payload['date']);
        self::assertSame('2024-05-25', $payload['supply_date']);
        self::assertSame('2024-06-14', $payload['due_date']);
        self::assertSame('2024-06-07', $payload['discount_date']);
    }

    #[Test]
    public function addItemAppendsItemsAndGetItemsReturnsThem(): void
    {
        $options = new InvoiceCreateOptions(clientId: 1);
        $item1 = new InvoiceItemCreateOptions(quantity: 1.0, unitPrice: 10.0);
        $item2 = new InvoiceItemCreateOptions(quantity: 2.0, unitPrice: 20.0);

        $options->addItem($item1)->addItem($item2);

        self::assertSame([$item1, $item2], $options->getItems());
    }

    #[Test]
    public function itEmbedsItemsUnderInvoiceItemsInvoiceItemKey(): void
    {
        $options = new InvoiceCreateOptions(clientId: 1);
        $item = new InvoiceItemCreateOptions(quantity: 3.0, unitPrice: 49.90);
        $item->title = 'Beratung';
        $item->type = InvoiceItemType::SERVICE;
        $options->addItem($item);

        $payload = $options->toArray();

        self::assertArrayHasKey('invoice-items', $payload);
        self::assertArrayHasKey('invoice-item', $payload['invoice-items']);
        self::assertCount(1, $payload['invoice-items']['invoice-item']);

        $serialised = $payload['invoice-items']['invoice-item'][0];
        self::assertSame('Beratung', $serialised['title']);
        self::assertSame(3.0, $serialised['quantity']);
        self::assertSame(49.90, $serialised['unit_price']);
        self::assertSame('SERVICE', $serialised['type']);
    }
}
