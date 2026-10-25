<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Filters;

use InvalidArgumentException;
use Justpilot\Billomat\Api\OrderBy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrderBy::class)]
final class OrderByTest extends TestCase
{
    #[Test]
    public function singleSortings(): void
    {
        self::assertSame('name+ASC', OrderBy::asc('name')->toQuery());
        self::assertSame('date+DESC', OrderBy::desc('date')->toQuery());
    }

    #[Test]
    public function nestedSortingsAreCommaSeparated(): void
    {
        $orderBy = OrderBy::desc('date')->thenAsc('invoice_number')->thenDesc('id');

        self::assertSame('date+DESC,invoice_number+ASC,id+DESC', $orderBy->toQuery());
    }

    #[Test]
    public function thenReturnsCopy(): void
    {
        $orderBy = OrderBy::asc('name');
        $orderBy->thenDesc('date');

        self::assertSame('name+ASC', $orderBy->toQuery());
    }

    #[Test]
    #[DataProvider('invalidFields')]
    public function rejectsInvalidFieldNames(string $field): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Ungültiges Sortierfeld "'.$field.'"');

        OrderBy::asc($field);
    }

    /**
     * @param callable(string): OrderBy $build
     */
    #[Test]
    #[DataProvider('builders')]
    public function everyBuilderValidatesTheField(callable $build): void
    {
        $this->expectException(InvalidArgumentException::class);

        $build('date DESC');
    }

    /**
     * @return iterable<string, array{callable(string): OrderBy}>
     */
    public static function builders(): iterable
    {
        yield 'asc' => [OrderBy::asc(...)];
        yield 'desc' => [OrderBy::desc(...)];
        yield 'thenAsc' => [static fn (string $field): OrderBy => OrderBy::asc('id')->thenAsc($field)];
        yield 'thenDesc' => [static fn (string $field): OrderBy => OrderBy::asc('id')->thenDesc($field)];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidFields(): iterable
    {
        yield 'leer' => [''];
        yield 'mit Richtung' => ['date DESC'];
        yield 'mit Plus' => ['date+DESC'];
        yield 'camelCase' => ['invoiceNumber'];
        yield 'Ziffer vorne' => ['1date'];
        yield 'Komma' => ['date,name'];
    }
}
