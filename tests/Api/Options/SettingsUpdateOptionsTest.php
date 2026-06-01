<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingsUpdateOptions::class)]
final class SettingsUpdateOptionsTest extends TestCase
{
    #[Test]
    public function emptyPayloadIsCompletelyStripped(): void
    {
        self::assertSame([], new SettingsUpdateOptions()->toArray());
    }

    #[Test]
    public function itSerializesEnumsAsStringValue(): void
    {
        $options = new SettingsUpdateOptions();
        $options->netGross = NetGross::NET;
        $options->numberRangeMode = NumberRangeMode::CONSIDER_PREFIX;

        $payload = $options->toArray();

        self::assertSame('NET', $payload['net_gross']);
        self::assertSame('CONSIDER_PREFIX', $payload['number_range_mode']);
    }

    #[Test]
    public function itEmitsPriceGroupKeysAsPriceGroupNFields(): void
    {
        $options = new SettingsUpdateOptions();
        $options->priceGroups = [2 => 'Großkunden', 3 => 'Wiederverkäufer'];

        $payload = $options->toArray();

        self::assertSame('Großkunden', $payload['price_group2']);
        self::assertSame('Wiederverkäufer', $payload['price_group3']);
        self::assertArrayNotHasKey('price_group1', $payload);
    }

    #[Test]
    public function itJoinsBccAddressesAsCsvString(): void
    {
        $options = new SettingsUpdateOptions();
        $options->bccAddresses = ['a@example.com', 'b@example.com', 'c@example.com'];

        $payload = $options->toArray();

        self::assertSame('a@example.com,b@example.com,c@example.com', $payload['bcc_addresses']);
    }

    #[Test]
    public function itOmitsBccAddressesWhenEmpty(): void
    {
        self::assertArrayNotHasKey('bcc_addresses', new SettingsUpdateOptions()->toArray());
    }

    #[Test]
    public function itSerializesPrintVersionAsOneOrZero(): void
    {
        $options = new SettingsUpdateOptions();
        $options->printVersion = true;
        self::assertSame(1, $options->toArray()['print_version']);

        $options->printVersion = false;
        self::assertSame(0, $options->toArray()['print_version']);
    }

    #[Test]
    public function itOmitsPrintVersionWhenNull(): void
    {
        self::assertArrayNotHasKey('print_version', new SettingsUpdateOptions()->toArray());
    }

    #[Test]
    public function itPassesScalarFieldsThrough(): void
    {
        $options = new SettingsUpdateOptions();
        $options->currencyCode = 'EUR';
        $options->locale = 'de_DE';
        $options->dueDays = 14;
        $options->discountRate = 2.5;
        $options->invoiceNumberPre = 'RE-';
        $options->reminderDueDays = 10;
        $options->taxation = 'gross';

        $payload = $options->toArray();

        self::assertSame('EUR', $payload['currency_code']);
        self::assertSame('de_DE', $payload['locale']);
        self::assertSame(14, $payload['due_days']);
        self::assertSame(2.5, $payload['discount_rate']);
        self::assertSame('RE-', $payload['invoice_number_pre']);
        self::assertSame(10, $payload['reminder_due_days']);
        self::assertSame('gross', $payload['taxation']);
    }
}
