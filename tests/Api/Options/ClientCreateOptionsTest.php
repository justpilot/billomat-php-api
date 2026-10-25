<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\ClientCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClientCreateOptions::class)]
final class ClientCreateOptionsTest extends TestCase
{
    #[Test]
    public function defaultPayloadIsEmpty(): void
    {
        // Alle Felder sind null → werden weggefiltert, auch dunning_run (Billomat-Default greift).
        $options = new ClientCreateOptions();

        self::assertSame([], $options->toArray());
    }

    #[Test]
    public function itStripsAllNullFields(): void
    {
        $options = new ClientCreateOptions(name: 'Acme', email: 'info@example.com');

        $payload = $options->toArray();

        self::assertSame('Acme', $payload['name']);
        self::assertSame('info@example.com', $payload['email']);
        self::assertArrayNotHasKey('street', $payload);
        self::assertArrayNotHasKey('zip', $payload);
        self::assertArrayNotHasKey('tax_number', $payload);
        self::assertArrayNotHasKey('bank_iban', $payload);
    }

    #[Test]
    public function itKeepsBooleanArchivedFlagEvenWhenFalse(): void
    {
        $options = new ClientCreateOptions(archived: false);

        self::assertFalse($options->toArray()['archived']);
    }

    #[Test]
    public function itSerializesAllSupportedFields(): void
    {
        $options = new ClientCreateOptions(
            name: 'Acme',
            clientNumber: 'K-001',
            number: 1,
            numberPre: 'K-',
            numberLength: 3,
            street: 'Musterstr. 1',
            zip: '12345',
            city: 'Berlin',
            countryCode: 'DE',
            firstName: 'Max',
            lastName: 'Mustermann',
            email: 'max@example.com',
            taxRule: 'TAX',
            netGross: 'NET',
            currencyCode: 'EUR',
            archived: true,
            dunningRun: true,
            reduction: 5.0,
            dueDays: 14,
            bankSwift: 'COBADEFFXXX',
            bankIban: 'DE89370400440532013000',
            sepaMandate: 'M-001',
            sepaMandateDate: '2024-01-01',
            defaultPaymentTypes: 'BANK_TRANSFER,CASH',
        );

        $payload = $options->toArray();

        self::assertSame('Acme', $payload['name']);
        self::assertTrue($payload['archived']);
        self::assertSame('K-', $payload['number_pre']);
        self::assertSame(1, $payload['number']);
        self::assertSame('DE', $payload['country_code']);
        self::assertSame('K-001', $payload['client_number']);
        self::assertSame('NET', $payload['net_gross']);
        self::assertSame('EUR', $payload['currency_code']);
        self::assertSame('DE89370400440532013000', $payload['bank_iban']);
        self::assertSame('2024-01-01', $payload['sepa_mandate_date']);
        self::assertSame('BANK_TRANSFER,CASH', $payload['default_payment_types']);
        self::assertSame(5.0, $payload['reduction']);
        self::assertSame(14, $payload['due_days']);
        self::assertTrue($payload['dunning_run']);
    }
}
