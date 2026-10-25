<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Internal;

use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListCaster::class)]
#[UsesClass(ScalarCaster::class)]
final class ListCasterTest extends TestCase
{
    /**
     * @return iterable<string, array{mixed, list<?int>}>
     */
    public static function listCases(): iterable
    {
        yield 'null' => [null, []];
        yield 'string' => ['x', []];
        yield 'leere Liste' => [[], []];
        yield 'einzelnes Objekt' => [['id' => '5'], [5]];
        yield 'Liste' => [[['id' => '5'], ['id' => '6']], [5, 6]];
        yield 'Nicht-Objekte werden übersprungen' => [[['id' => '5'], 'x', 7], [5]];
        yield 'Schlüssel werden verworfen' => [['a' => ['id' => '5'], 'b' => ['id' => '6']], [5, 6]];
    }

    /**
     * @param list<?int> $expected
     */
    #[Test]
    #[DataProvider('listCases')]
    public function toListNormalizesBillomatLists(mixed $value, array $expected): void
    {
        $ids = ListCaster::toList($value, 'id', static fn (array $row): ?int => ScalarCaster::toIntOrNull($row['id'] ?? null));

        self::assertSame($expected, $ids);
    }
}
