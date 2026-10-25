<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\AccessLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccessLevel::class)]
final class AccessLevelTest extends TestCase
{
    #[Test]
    public function itExposesExpectedCases(): void
    {
        self::assertSame(
            ['READ', 'UPDATE', 'DELETE'],
            array_map(static fn (AccessLevel $c): string => $c->value, AccessLevel::cases())
        );
    }

    #[Test]
    public function itHasGermanLabels(): void
    {
        self::assertSame('Lesen', AccessLevel::READ->label());
        self::assertSame('Bearbeiten', AccessLevel::UPDATE->label());
        self::assertSame('Löschen', AccessLevel::DELETE->label());
    }

    /**
     * @return iterable<string, array{AccessLevel, AccessLevel, bool}>
     */
    public static function allowsCases(): iterable
    {
        yield 'READ erlaubt READ' => [AccessLevel::READ, AccessLevel::READ, true];
        yield 'READ erlaubt kein UPDATE' => [AccessLevel::READ, AccessLevel::UPDATE, false];
        yield 'READ erlaubt kein DELETE' => [AccessLevel::READ, AccessLevel::DELETE, false];
        yield 'UPDATE erlaubt READ' => [AccessLevel::UPDATE, AccessLevel::READ, true];
        yield 'UPDATE erlaubt UPDATE' => [AccessLevel::UPDATE, AccessLevel::UPDATE, true];
        yield 'UPDATE erlaubt kein DELETE' => [AccessLevel::UPDATE, AccessLevel::DELETE, false];
        yield 'DELETE erlaubt READ' => [AccessLevel::DELETE, AccessLevel::READ, true];
        yield 'DELETE erlaubt UPDATE' => [AccessLevel::DELETE, AccessLevel::UPDATE, true];
        yield 'DELETE erlaubt DELETE' => [AccessLevel::DELETE, AccessLevel::DELETE, true];
    }

    #[Test]
    #[DataProvider('allowsCases')]
    public function higherLevelsIncludeLowerOnes(AccessLevel $level, AccessLevel $required, bool $expected): void
    {
        self::assertSame($expected, $level->allows($required));
    }
}
