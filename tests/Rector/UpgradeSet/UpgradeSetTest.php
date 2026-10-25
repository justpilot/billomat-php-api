<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Rector\UpgradeSet;

use Iterator;
use Justpilot\Billomat\Rector\GetItemsToPropertyRector;
use Justpilot\Billomat\Rector\NestedSubResourceRector;
use Justpilot\Billomat\Rector\SubResourceMap;
use Justpilot\Billomat\Rector\SubResourceMethodRector;
use Justpilot\Billomat\Tests\Rector\AbstractIsolatedRectorTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Ende-zu-Ende: das ausgelieferte Set `rector/upgrade-4.0.php` auf 3.x-Code.
 */
#[CoversClass(NestedSubResourceRector::class)]
#[CoversClass(SubResourceMethodRector::class)]
#[CoversClass(GetItemsToPropertyRector::class)]
#[CoversClass(SubResourceMap::class)]
final class UpgradeSetTest extends AbstractIsolatedRectorTestCase
{
    #[Test]
    #[DataProvider('provideFixtures')]
    public function itUpgrades3xCode(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    /**
     * @return Iterator<array-key, array<int, string>>
     */
    public static function provideFixtures(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture');
    }

    #[Override]
    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/upgrade_with_imports.php';
    }
}
