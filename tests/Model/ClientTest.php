<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
final class ClientTest extends TestCase
{
    #[Test]
    public function itHydratesFullClientFromArray(): void
    {
        $client = Client::fromArray([
            'id' => '42',
            'name' => 'Acme GmbH',
            'created' => '2024-01-15T10:30:00+01:00',
            'client_number' => 'K-001',
            'number' => '1',
            'number_pre' => 'K-',
            'number_length' => '3',
            'street' => 'Musterstraße 1',
            'zip' => '12345',
            'city' => 'Berlin',
            'state' => 'BE',
            'country_code' => 'DE',
            'first_name' => 'Max',
            'last_name' => 'Mustermann',
            'email' => 'max@example.com',
            'tax_rule' => 'TAX',
            'net_gross' => 'NET',
            'currency_code' => 'EUR',
            'archived' => '1',
            'dunning_run' => '0',
            'reduction' => '5.5',
            'bank_iban' => 'DE89370400440532013000',
            'bank_swift' => 'COBADEFFXXX',
            'sepa_mandate' => 'M-001',
            'sepa_mandate_date' => '2023-12-01',
            'default_payment_types' => 'BANK_TRANSFER,CASH',
            'revenue_gross' => '11900',
            'revenue_net' => '10000',
        ]);

        self::assertSame(42, $client->id);
        self::assertSame('Acme GmbH', $client->name);
        self::assertInstanceOf(DateTimeImmutable::class, $client->created);
        self::assertSame('K-001', $client->clientNumber);
        self::assertSame(1, $client->number);
        self::assertSame(3, $client->numberLength);
        self::assertSame('Berlin', $client->city);
        self::assertSame('DE', $client->countryCode);
        self::assertTrue($client->archived);
        self::assertFalse($client->dunningRun);
        self::assertSame(5.5, $client->reduction);
        self::assertSame('DE89370400440532013000', $client->bankIban);
        self::assertInstanceOf(DateTimeImmutable::class, $client->sepaMandateDate);
        self::assertSame('2023-12-01', $client->sepaMandateDate->format('Y-m-d'));
        self::assertSame('BANK_TRANSFER,CASH', $client->defaultPaymentTypes);
        self::assertSame(11900.0, $client->revenueGross);
    }

    #[Test]
    public function itFiltersEmptyStringsAsNullForNumericFields(): void
    {
        // Billomat liefert leere optionale Felder häufig als ""; Cast zu (int) würde sonst 0 ergeben.
        $client = Client::fromArray([
            'id' => '1',
            'name' => 'Test',
            'number' => '',
            'number_length' => '',
            'debitor_account_number' => '',
            'price_group' => '',
            'reduction' => '',
            'discount_rate' => '',
            'due_days' => '',
            'reminder_due_days' => '',
            'offer_validity_days' => '',
            'revenue_gross' => '',
            'revenue_net' => '',
        ]);

        self::assertNull($client->number);
        self::assertNull($client->numberLength);
        self::assertNull($client->debitorAccountNumber);
        self::assertNull($client->priceGroup);
        self::assertNull($client->reduction);
        self::assertNull($client->discountRate);
        self::assertNull($client->dueDays);
        self::assertNull($client->reminderDueDays);
        self::assertNull($client->offerValidityDays);
        self::assertNull($client->revenueGross);
        self::assertNull($client->revenueNet);
    }

    #[Test]
    public function itDefaultsArchivedAndDunningRunToNullWhenAbsent(): void
    {
        $client = Client::fromArray(['id' => 1, 'name' => 'Min']);

        self::assertNull($client->archived);
        self::assertNull($client->dunningRun);
        self::assertNull($client->created);
    }

    #[Test]
    public function itHandlesInvalidCreatedDateGracefully(): void
    {
        $client = Client::fromArray([
            'id' => 1,
            'name' => 'Min',
            'created' => 'not-a-date',
        ]);

        self::assertNull($client->created);
    }

    #[Test]
    public function toArrayMapsCamelCaseBackToSnakeCase(): void
    {
        $client = Client::fromArray([
            'id' => 42,
            'name' => 'Acme',
            'client_number' => 'K-001',
            'country_code' => 'DE',
            'bank_iban' => 'DE89370400440532013000',
            'sepa_mandate_date' => '2023-12-01',
        ]);

        $array = $client->toArray();

        self::assertSame(42, $array['id']);
        self::assertSame('Acme', $array['name']);
        self::assertSame('K-001', $array['client_number']);
        self::assertSame('DE', $array['country_code']);
        self::assertSame('DE89370400440532013000', $array['bank_iban']);
        self::assertSame('2023-12-01', $array['sepa_mandate_date']);
    }
}
