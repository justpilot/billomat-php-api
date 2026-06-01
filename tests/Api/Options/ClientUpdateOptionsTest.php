<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\ClientUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClientUpdateOptions::class)]
final class ClientUpdateOptionsTest extends TestCase
{
    #[Test]
    public function emptyPayloadIsCompletelyStripped(): void
    {
        // Bei Update sind alle Felder optional — kein Default-Flag bleibt drin
        self::assertSame([], new ClientUpdateOptions()->toArray());
    }

    #[Test]
    public function itSerializesArchivedAndDunningRunAsOneOrZero(): void
    {
        $options = new ClientUpdateOptions();
        $options->archived = true;
        $options->dunningRun = false;

        $payload = $options->toArray();

        self::assertSame(1, $payload['archived']);
        self::assertSame(0, $payload['dunning_run']);
    }

    #[Test]
    public function itMapsCamelCaseToSnakeCase(): void
    {
        $options = new ClientUpdateOptions();
        $options->name = 'Acme';
        $options->countryCode = 'DE';
        $options->firstName = 'Max';
        $options->lastName = 'Mustermann';
        $options->bankIban = 'DE89370400440532013000';
        $options->sepaMandate = 'M-001';
        $options->sepaMandateDate = '2024-01-01';
        $options->dueDays = 14;
        $options->discountRate = 2.5;

        $payload = $options->toArray();

        self::assertSame('Acme', $payload['name']);
        self::assertSame('DE', $payload['country_code']);
        self::assertSame('Max', $payload['first_name']);
        self::assertSame('Mustermann', $payload['last_name']);
        self::assertSame('DE89370400440532013000', $payload['bank_iban']);
        self::assertSame('M-001', $payload['sepa_mandate']);
        self::assertSame('2024-01-01', $payload['sepa_mandate_date']);
        self::assertSame(14, $payload['due_days']);
        self::assertSame(2.5, $payload['discount_rate']);
    }
}
