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
        $options = new ClientUpdateOptions(archived: true, dunningRun: false);

        $payload = $options->toArray();

        self::assertSame(1, $payload['archived']);
        self::assertSame(0, $payload['dunning_run']);
    }

    #[Test]
    public function itMapsCamelCaseToSnakeCase(): void
    {
        $options = new ClientUpdateOptions(
            name: 'Acme',
            countryCode: 'DE',
            firstName: 'Max',
            lastName: 'Mustermann',
            discountRate: 2.5,
            dueDays: 14,
            bankIban: 'DE89370400440532013000',
            sepaMandate: 'M-001',
            sepaMandateDate: '2024-01-01',
        );

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
