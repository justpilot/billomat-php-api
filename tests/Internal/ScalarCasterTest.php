<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Internal;

use DateTimeImmutable;
use Justpilot\Billomat\Internal\ScalarCaster;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

use const DATE_ATOM;

#[CoversClass(ScalarCaster::class)]
final class ScalarCasterTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed, ?int}>
     */
    public static function intCases(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        yield 'numeric string' => ['42', 42];
        yield 'negative numeric string' => ['-7', -7];
        yield 'int' => [99, 99];
        yield 'float' => [3.9, 3];
        yield 'bool true' => [true, 1];
        yield 'bool false' => [false, 0];
        yield 'array' => [[1, 2], null];
        yield 'object' => [new stdClass(), null];
    }

    #[Test]
    #[DataProvider('intCases')]
    public function toIntOrNullCovers(mixed $value, ?int $expected): void
    {
        self::assertSame($expected, ScalarCaster::toIntOrNull($value));
    }

    /**
     * @return iterable<string, array{mixed, ?float}>
     */
    public static function floatCases(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        yield 'numeric string' => ['1.5', 1.5];
        yield 'int' => [4, 4.0];
        yield 'float' => [2.5, 2.5];
        yield 'bool true' => [true, 1.0];
        yield 'array' => [[], null];
    }

    #[Test]
    #[DataProvider('floatCases')]
    public function toFloatOrNullCovers(mixed $value, ?float $expected): void
    {
        self::assertSame($expected, ScalarCaster::toFloatOrNull($value));
    }

    /**
     * @return iterable<string, array{mixed, ?bool}>
     */
    public static function boolCases(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        yield 'string "1"' => ['1', true];
        yield 'string "0"' => ['0', false];
        yield 'string "true"' => ['true', true];
        yield 'string "false"' => ['false', false];
        yield 'string "off"' => ['off', false];
        yield 'string "no"' => ['no', false];
        yield 'string "yes"' => ['yes', true];
        yield 'int 1' => [1, true];
        yield 'int 0' => [0, false];
        yield 'bool true' => [true, true];
        yield 'array' => [[], null];
    }

    #[Test]
    #[DataProvider('boolCases')]
    public function toBoolOrNullCovers(mixed $value, ?bool $expected): void
    {
        self::assertSame($expected, ScalarCaster::toBoolOrNull($value));
    }

    /**
     * @return iterable<string, array{mixed, ?string}>
     */
    public static function stringCases(): iterable
    {
        yield 'null' => [null, null];
        yield 'empty string -> null' => ['', null];
        yield 'non-empty string' => ['abc', 'abc'];
        yield 'int' => [7, '7'];
        yield 'float' => [1.5, '1.5'];
        yield 'bool true' => [true, '1'];
        yield 'array' => [[], null];
    }

    #[Test]
    #[DataProvider('stringCases')]
    public function toStringOrNullCovers(mixed $value, ?string $expected): void
    {
        self::assertSame($expected, ScalarCaster::toStringOrNull($value));
    }

    #[Test]
    public function toDateTimeOrNullReturnsNullForEmptyOrNonString(): void
    {
        self::assertNull(ScalarCaster::toDateTimeOrNull(null));
        self::assertNull(ScalarCaster::toDateTimeOrNull(''));
        self::assertNull(ScalarCaster::toDateTimeOrNull('   '));
        self::assertNull(ScalarCaster::toDateTimeOrNull(123));
        self::assertNull(ScalarCaster::toDateTimeOrNull([]));
    }

    #[Test]
    public function toDateTimeOrNullParsesDateString(): void
    {
        $result = ScalarCaster::toDateTimeOrNull('2026-06-02');

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        self::assertSame('2026-06-02', $result->format('Y-m-d'));
    }

    #[Test]
    public function toDateTimeOrNullParsesIsoTimestamp(): void
    {
        $result = ScalarCaster::toDateTimeOrNull('2026-06-02T10:15:00+02:00');

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        self::assertSame('2026-06-02T10:15:00+02:00', $result->format(DATE_ATOM));
    }

    #[Test]
    public function toDateTimeOrNullSwallowsParseErrors(): void
    {
        self::assertNull(ScalarCaster::toDateTimeOrNull('definitely not a date'));
    }
}
