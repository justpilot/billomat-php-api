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
    public function defaultPayloadContainsOnlyDunningRun(): void
    {
        // Alle anderen Felder sind null → werden weggefiltert.
        // dunning_run hat den Default false und bleibt drin.
        $options = new ClientCreateOptions();

        self::assertSame(['dunning_run' => false], $options->toArray());
    }

    #[Test]
    public function itStripsAllNullFields(): void
    {
        $options = new ClientCreateOptions();
        $options->name = 'Acme';
        $options->email = 'info@example.com';

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
        $options = new ClientCreateOptions();
        $options->archived = false;

        self::assertFalse($options->toArray()['archived']);
    }

    #[Test]
    public function itSerializesAllSupportedFields(): void
    {
        $options = new ClientCreateOptions();
        $options->name = 'Acme';
        $options->archived = true;
        $options->numberPre = 'K-';
        $options->number = 1;
        $options->numberLength = 3;
        $options->street = 'Musterstr. 1';
        $options->zip = '12345';
        $options->city = 'Berlin';
        $options->countryCode = 'DE';
        $options->clientNumber = 'K-001';
        $options->firstName = 'Max';
        $options->lastName = 'Mustermann';
        $options->email = 'max@example.com';
        $options->taxRule = 'TAX';
        $options->netGross = 'NET';
        $options->currencyCode = 'EUR';
        $options->bankIban = 'DE89370400440532013000';
        $options->bankSwift = 'COBADEFFXXX';
        $options->sepaMandate = 'M-001';
        $options->sepaMandateDate = '2024-01-01';
        $options->defaultPaymentTypes = 'BANK_TRANSFER,CASH';
        $options->reduction = 5.0;
        $options->dueDays = 14;
        $options->dunningRun = true;

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
