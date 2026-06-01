<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use DateTimeImmutable;
use Justpilot\Billomat\Api\InvoiceUpdateOptions;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceUpdateOptions::class)]
final class InvoiceUpdateOptionsTest extends TestCase
{
    #[Test]
    public function emptyPayloadIsCompletelyStripped(): void
    {
        self::assertSame([], new InvoiceUpdateOptions()->toArray());
    }

    #[Test]
    public function itFormatsDatesAsIsoLocalDate(): void
    {
        $options = new InvoiceUpdateOptions();
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
    public function itSerializesEnumsAsStringValue(): void
    {
        $options = new InvoiceUpdateOptions();
        $options->netGross = NetGross::GROSS;
        $options->supplyDateType = SupplyDateType::DELIVERY_DATE;

        $payload = $options->toArray();

        self::assertSame('GROSS', $payload['net_gross']);
        self::assertSame('DELIVERY_DATE', $payload['supply_date_type']);
    }

    #[Test]
    public function itPassesScalarFieldsThrough(): void
    {
        $options = new InvoiceUpdateOptions();
        $options->clientId = 7;
        $options->title = 'Korrektur';
        $options->paymentTypes = 'BANK_TRANSFER,CASH';
        $options->templateId = 4;
        $options->quote = 1.1234;

        $payload = $options->toArray();

        self::assertSame(7, $payload['client_id']);
        self::assertSame('Korrektur', $payload['title']);
        self::assertSame('BANK_TRANSFER,CASH', $payload['payment_types']);
        self::assertSame(4, $payload['template_id']);
        self::assertSame(1.1234, $payload['quote']);
    }
}
