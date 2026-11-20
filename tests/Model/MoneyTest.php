<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use BcMath\Number;
use InvalidArgumentException;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RoundingMode;

#[CoversClass(Money::class)]
final class MoneyTest extends TestCase
{
    #[Test]
    #[DataProvider('canonicalValues')]
    public function toStringIsCanonical(string|int $input, string $expected): void
    {
        self::assertSame($expected, Money::of($input)->toString());
        self::assertSame($expected, (string) Money::of($input));
        self::assertSame('"'.$expected.'"', json_encode(Money::of($input)));
    }

    /**
     * @return iterable<string, array{string|int, string}>
     */
    public static function canonicalValues(): iterable
    {
        yield 'Ganzzahl' => [40, '40'];
        yield 'negative Ganzzahl' => [-3, '-3'];
        yield 'nachgestellte Nullen' => ['40.00', '40'];
        yield 'eine Nachkommastelle' => ['0.10', '0.1'];
        yield 'vier Nachkommastellen' => ['12.3456', '12.3456'];
        yield 'negativ' => ['-12.3456', '-12.3456'];
        yield 'negative Null' => ['-0.00', '0'];
        yield 'Null in der Mitte' => ['100.05', '100.05'];
        yield 'führende Nullen' => ['007.50', '7.5'];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function rejectsInvalidAmounts(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Ungültiger Betrag "'.$input.'": erwartet wird eine Dezimalzahl mit Punkt, z. B. "19.99".');

        Money::of($input);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidValues(): iterable
    {
        yield 'leer' => [''];
        yield 'Komma' => ['19,99'];
        yield 'Exponent' => ['1e3'];
        yield 'Leerzeichen' => [' 1'];
        yield 'Text' => ['zehn'];
    }

    #[Test]
    public function ofAcceptsMoneyAndNumber(): void
    {
        $money = Money::of('5');

        self::assertSame($money, Money::of($money));
        self::assertSame('2.5', Money::of(new Number('2.50'))->toString());
        self::assertSame('2.50', Money::of('2.50')->toNumber()->value);
    }

    #[Test]
    public function arithmeticIsExact(): void
    {
        self::assertSame('0.3', Money::of('0.1')->add('0.2')->toString());
        self::assertSame('-15.5', Money::of('10')->subtract(Money::of('25.5'))->toString());
        self::assertSame('59.97', Money::of('19.99')->multiply(3)->toString());
        self::assertSame('7.4074', Money::of('2.1164')->multiply('3.5')->toString());
        self::assertSame('-19.99', Money::of('19.99')->negate()->toString());
        self::assertSame('19.99', Money::of('-19.99')->negate()->toString());
        self::assertSame('19.99', Money::of('-19.99')->abs()->toString());
        self::assertSame('19.99', Money::of('19.99')->abs()->toString());
        self::assertSame('0', Money::zero()->toString());
    }

    #[Test]
    public function sumIgnoresNull(): void
    {
        self::assertSame('35.5', Money::sum(Money::of('10'), null, Money::of('25.5'))->toString());
        self::assertTrue(Money::sum(Money::of('-5'), Money::of('5'))->isZero());
        self::assertSame('0', Money::sum()->toString());
    }

    #[Test]
    public function roundsHalfAwayFromZeroByDefault(): void
    {
        self::assertSame('0.01', Money::of('0.005')->round()->toString());
        self::assertSame('-0.01', Money::of('-0.005')->round()->toString());
        self::assertSame('12.35', Money::of('12.3456')->round()->toString());
        self::assertSame('12.3', Money::of('12.3456')->round(1)->toString());
        self::assertSame('0', Money::of('0.005')->round(2, RoundingMode::HalfEven)->toString());
    }

    #[Test]
    public function comparesByValue(): void
    {
        self::assertTrue(Money::of('40')->equals('40.00'));
        self::assertFalse(Money::of('40')->equals('40.01'));
        self::assertSame(-1, Money::of('-1')->compareTo(0));
        self::assertSame(0, Money::of('1.0')->compareTo(1));
        self::assertSame(1, Money::of('1.01')->compareTo(Money::of('1')));
        self::assertTrue(Money::of('2')->isGreaterThan('1.99'));
        self::assertFalse(Money::of('2')->isGreaterThan('2'));
        self::assertTrue(Money::of('1.99')->isLessThan('2'));
        self::assertFalse(Money::of('2')->isLessThan('2'));
    }

    #[Test]
    public function signPredicates(): void
    {
        self::assertTrue(Money::of('0.00')->isZero());
        self::assertFalse(Money::of('0.01')->isZero());
        self::assertTrue(Money::of('0.01')->isPositive());
        self::assertFalse(Money::of('0')->isPositive());
        self::assertFalse(Money::of('-0.01')->isPositive());
        self::assertTrue(Money::of('-0.01')->isNegative());
        self::assertFalse(Money::of('0')->isNegative());
        self::assertFalse(Money::of('0.01')->isNegative());
    }

    #[Test]
    #[DataProvider('formats')]
    public function formatsWithFixedDecimals(string $amount, int $decimals, string $decimalSeparator, string $thousandsSeparator, string $expected): void
    {
        self::assertSame($expected, Money::of($amount)->format($decimals, $decimalSeparator, $thousandsSeparator));
    }

    /**
     * @return iterable<string, array{string, int, string, string, string}>
     */
    public static function formats(): iterable
    {
        yield 'auffüllen' => ['40', 2, '.', '', '40.00'];
        yield 'runden' => ['12.3456', 2, '.', '', '12.35'];
        yield 'ohne Nachkommastellen' => ['1234.5', 0, ',', '.', '1.235'];
        yield 'deutsch' => ['1234.5', 2, ',', '.', '1.234,50'];
        yield 'negativ deutsch' => ['-1234567.005', 2, ',', '.', '-1.234.567,01'];
        yield 'drei Stellen ohne Tausender' => ['123', 2, ',', '.', '123,00'];
        yield 'genau sechs Stellen' => ['123456', 2, '.', ' ', '123 456.00'];
        yield 'keine negative Null' => ['-0.004', 2, '.', '', '0.00'];
        yield 'mehrstelliges Trennzeichen' => ['1234567', 0, '.', "\u{202F}", "1\u{202F}234\u{202F}567"];
    }

    #[Test]
    public function formatDefaultsToTwoDecimalsWithPoint(): void
    {
        self::assertSame('1234.50', Money::of('1234.5')->format());
    }

    #[Test]
    public function toFloatIsAnEscapeHatch(): void
    {
        self::assertSame(19.99, Money::of('19.99')->toFloat());
        self::assertSame(-0.5, Money::of('-0.50')->toFloat());
    }
}
