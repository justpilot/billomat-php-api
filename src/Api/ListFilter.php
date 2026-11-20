<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Filter für eine Billomat-Liste (GET /clients, GET /invoices, …).
 *
 * Die `*Filter`-Klassen sind unveränderlich: Felder per benannten Argumenten
 * setzen oder mit `with*()` eine geänderte Kopie erzeugen. Nicht gesetzte
 * Felder (null bzw. leere Liste) fehlen im Query.
 *
 * ```php
 * $billomat->invoices->list(new InvoiceFilter(
 *     status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
 *     orderBy: OrderBy::desc('date'),
 * ));
 * ```
 */
interface ListFilter
{
    /**
     * Query-Parameter im Billomat-Format (snake_case, Mehrfachwerte kommasepariert).
     *
     * @return array<string, scalar|array<scalar>>
     */
    public function toQuery(): array;
}
