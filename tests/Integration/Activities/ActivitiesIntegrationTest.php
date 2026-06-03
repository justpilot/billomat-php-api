<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Activities;

use Justpilot\Billomat\Model\Activity;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ActivitiesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canListActivitiesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $activities = $billomat->activities->list(['per_page' => 5]);

        self::assertIsArray($activities);
        self::assertContainsOnlyInstancesOf(Activity::class, $activities);
        self::assertLessThanOrEqual(5, \count($activities));
    }

    #[Group('integration')]
    #[Test]
    public function canFetchSinglePageWithPaginationMetadata(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $page = $billomat->activities->listPage(['page' => 1, 'per_page' => 5]);

        self::assertSame(1, $page->info->page);
        self::assertLessThanOrEqual(5, \count($page->items));
    }
}
