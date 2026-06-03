<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Search;

use Justpilot\Billomat\Model\SearchResult;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class SearchIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canQuerySandboxAndReceiveResultShape(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        // „a" trifft erfahrungsgemäß irgendetwas in einer aktiven Sandbox;
        // wir prüfen Form und Typ, nicht den fachlichen Inhalt.
        $hits = $billomat->search->query('a');

        self::assertIsArray($hits);
        self::assertContainsOnlyInstancesOf(SearchResult::class, $hits);

        foreach ($hits as $hit) {
            self::assertNotSame('', $hit->resource);
        }
    }

    #[Group('integration')]
    #[Test]
    public function emptyQueryYieldsEmptyOrLargeListWithoutError(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $hits = $billomat->search->query('zzz-unlikely-token-xyz');

        self::assertIsArray($hits);
        self::assertContainsOnlyInstancesOf(SearchResult::class, $hits);
    }
}
