<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Rector\GetItemsToPropertyRector;

use Iterator;
use Justpilot\Billomat\Rector\GetItemsToPropertyRector;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

#[CoversClass(GetItemsToPropertyRector::class)]
final class GetItemsToPropertyRectorTest extends AbstractRectorTestCase
{
    #[Test]
    #[DataProvider('provideFixtures')]
    public function itUpgradesTheFixture(string $filePath): void
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
        return __DIR__.'/config/configured_rule.php';
    }
}
