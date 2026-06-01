<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;
use Justpilot\Billomat\Model\Enum\TemplateEngine;
use Justpilot\Billomat\Model\Settings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Settings::class)]
final class SettingsTest extends TestCase
{
    #[Test]
    public function itHydratesCoreFieldsAndEnums(): void
    {
        $settings = Settings::fromArray([
            'created' => '2020-01-15T10:00:00+01:00',
            'updated' => '2024-05-30T18:00:00+02:00',
            'currency_code' => 'EUR',
            'locale' => 'de_DE',
            'net_gross' => 'NET',
            'number_range_mode' => 'IGNORE_PREFIX',
            'template_engine' => 'DEFAULT',
            'invoice_number_pre' => 'RE-',
            'invoice_number_length' => '5',
            'invoice_number_next' => '102',
            'due_days' => '14',
            'discount_rate' => '2.5',
            'print_version' => '1',
            'taxation' => 'gross',
        ]);

        self::assertInstanceOf(DateTimeImmutable::class, $settings->created);
        self::assertInstanceOf(DateTimeImmutable::class, $settings->updated);
        self::assertSame('EUR', $settings->currencyCode);
        self::assertSame('de_DE', $settings->locale);
        self::assertSame(NetGross::NET, $settings->netGross);
        self::assertSame(NumberRangeMode::IGNORE_PREFIX, $settings->numberRangeMode);
        self::assertSame(TemplateEngine::DEFAULT, $settings->templateEngine);
        self::assertSame(5, $settings->invoiceNumberLength);
        self::assertSame(102, $settings->invoiceNumberNext);
        self::assertSame(14, $settings->dueDays);
        self::assertSame(2.5, $settings->discountRate);
        self::assertTrue($settings->printVersion);
    }

    #[Test]
    public function itCollectsPriceGroupKeysIntoIndexedArray(): void
    {
        $settings = Settings::fromArray([
            'price_group2' => 'Großkunden',
            'price_group3' => 'Wiederverkäufer',
            'price_group7' => 'Sonderkunden',
        ]);

        self::assertSame(
            [2 => 'Großkunden', 3 => 'Wiederverkäufer', 7 => 'Sonderkunden'],
            $settings->priceGroups
        );
    }

    #[Test]
    public function itIgnoresPriceGroupKeysThatAreNotStrings(): void
    {
        $settings = Settings::fromArray([
            'price_group2' => ['nested' => 'no'],
            'price_group3' => null,
        ]);

        self::assertSame([], $settings->priceGroups);
    }

    /**
     * @param list<string> $expected
     */
    #[Test]
    #[DataProvider('bccProvider')]
    public function itParsesBccAddressesFromVariousShapes(mixed $rawBcc, array $expected): void
    {
        $settings = Settings::fromArray(['bcc_addresses' => $rawBcc]);

        self::assertSame($expected, $settings->bccAddresses);
    }

    /**
     * @return iterable<string, array{0: mixed, 1: list<string>}>
     */
    public static function bccProvider(): iterable
    {
        yield 'CSV-String' => [
            'a@example.com, b@example.com , c@example.com',
            ['a@example.com', 'b@example.com', 'c@example.com'],
        ];
        yield 'leerer String' => ['', []];
        yield 'String mit nur Trennzeichen' => [' , , ', []];
        yield 'Single-Element' => ['only@example.com', ['only@example.com']];
        yield 'Tags-Form als Array' => [
            ['bcc_address' => ['x@example.com', 'y@example.com']],
            ['x@example.com', 'y@example.com'],
        ];
        yield 'Tags-Form mit einzelnem String' => [
            ['bcc_address' => 'single@example.com'],
            ['single@example.com'],
        ];
        yield 'null' => [null, []];
    }

    #[Test]
    public function itTreatsEmptyStringAsNullForNumericFields(): void
    {
        $settings = Settings::fromArray([
            'due_days' => '',
            'discount_rate' => '',
            'print_version' => '',
        ]);

        self::assertNull($settings->dueDays);
        self::assertNull($settings->discountRate);
        self::assertNull($settings->printVersion);
    }

    #[Test]
    public function itAcceptsBoolValuesForFlagFields(): void
    {
        $settings = Settings::fromArray(['print_version' => true]);

        self::assertTrue($settings->printVersion);
    }

    #[Test]
    public function itHandlesUnknownEnumValuesAsNull(): void
    {
        $settings = Settings::fromArray([
            'net_gross' => 'WAT',
            'number_range_mode' => 'BOGUS',
        ]);

        self::assertNull($settings->netGross);
        self::assertNull($settings->numberRangeMode);
    }
}
