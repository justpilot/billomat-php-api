<?php

/**
 * Beispiel 05: Filter, Pagination und Sortierung beim Listen.
 *
 * Demonstriert die typisierten Filter (`ClientFilter`, `InvoiceFilter`, …):
 *  - OrderBy::desc('date') wird zu order_by=date+DESC (das "+" bleibt literal,
 *    nicht "%2B", siehe docs/advanced/http-layer.md)
 *  - Mehrfachwerte (z. B. status=OPEN,OVERDUE) werden kommasepariert gesendet
 *
 * Aufruf:
 *  php examples/05-list-with-filters.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

$billomat = billomat();

// 1) Die fünf zuletzt angelegten Kunden
$recentClients = $billomat->clients->list(new ClientFilter(orderBy: OrderBy::desc('id'), perPage: 5));

echo "Zuletzt angelegte Kunden:\n";
foreach ($recentClients as $client) {
    printf("  #%d  %s\n", $client->id ?? 0, $client->name);
}

// 2) Rechnungen mit mehreren Status-Werten, nach Datum und Nummer sortiert
$openOrOverdue = $billomat->invoices->list(new InvoiceFilter(
    status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
    orderBy: OrderBy::desc('date')->thenAsc('invoice_number'),
    perPage: 10,
));

echo "\nOffene oder überfällige Rechnungen (max. 10):\n";
foreach ($openOrOverdue as $invoice) {
    printf(
        "  #%d  %s  %s  offen: %s %s\n",
        $invoice->id ?? 0,
        $invoice->invoiceNumber ?? '?',
        $invoice->status?->label() ?? '?',
        $invoice->openAmount?->format(2, ',', '.') ?? '?',
        $invoice->currencyCode ?? '',
    );
}
