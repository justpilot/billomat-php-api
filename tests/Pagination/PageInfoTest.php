<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Pagination;

use Justpilot\Billomat\Pagination\PageInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageInfo::class)]
final class PageInfoTest extends TestCase
{
    #[Test]
    public function totalPagesRoundsUp(): void
    {
        self::assertSame(3, new PageInfo(page: 1, perPage: 50, total: 120)->totalPages());
        self::assertSame(1, new PageInfo(page: 1, perPage: 50, total: 50)->totalPages());
        self::assertSame(1, new PageInfo(page: 1, perPage: 50, total: 1)->totalPages());
        self::assertSame(0, new PageInfo(page: 1, perPage: 50, total: 0)->totalPages());
    }

    #[Test]
    public function totalPagesIsNullWhenTotalUnknown(): void
    {
        self::assertNull(new PageInfo(page: 1, perPage: 50, total: null)->totalPages());
    }

    #[Test]
    public function totalPagesGuardsAgainstZeroPerPage(): void
    {
        // Endpunkt ohne Pagination-Metadaten — keine Division-by-zero
        self::assertSame(1, new PageInfo(page: 1, perPage: 0, total: 5)->totalPages());
        self::assertSame(1, new PageInfo(page: 1, perPage: -1, total: 5)->totalPages());
    }

    #[Test]
    public function hasNextPageReflectsCurrentPosition(): void
    {
        $first = new PageInfo(page: 1, perPage: 50, total: 120);
        $middle = new PageInfo(page: 2, perPage: 50, total: 120);
        $last = new PageInfo(page: 3, perPage: 50, total: 120);

        self::assertTrue($first->hasNextPage());
        self::assertTrue($middle->hasNextPage());
        self::assertFalse($last->hasNextPage());
    }

    #[Test]
    public function hasNextPageOptimisticallyTrueWhenTotalUnknown(): void
    {
        // Ohne @total kann der Iterator nicht früh aufhören — er verlässt
        // sich auf den count(items) < pageSize-Fallback.
        self::assertTrue(new PageInfo(page: 1, perPage: 50, total: null)->hasNextPage());
        self::assertTrue(new PageInfo(page: 99, perPage: 50, total: null)->hasNextPage());
    }
}
