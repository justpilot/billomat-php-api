<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Pagination;

/**
 * Eine einzelne Seite einer paginierten Billomat-List-Response.
 *
 * Wird von `AbstractApi::listResourcePage()` und den darauf aufbauenden
 * `*Api::listPage()`-Methoden zurückgegeben. Für lazy Iteration über alle
 * Seiten siehe stattdessen `*Api::iterateAll()`.
 *
 * @template T of object
 */
final readonly class Page
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public PageInfo $info,
    ) {
    }
}
