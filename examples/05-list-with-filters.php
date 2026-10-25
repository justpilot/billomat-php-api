<?php

/**
 * Beispiel 05: Filter, Pagination und Sortierung beim Listen.
 *
 * Demonstriert die typisierten Filter (`ClientFilter`, `InvoiceFilter`, …):
 *  - OrderBy::desc('date') wird zu order_by=date+DESC (das "+" bleibt literal,
 *    nicht "%2B" — siehe docs/advanced/http-layer.md)
 *  - Mehrfachwerte (z. B. status=OPEN,OVERDUE) werden kommasepariert gesendet
 *
 * Aufruf:
 *  php examples/05-list-with-filters.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;

if (!$billomatId || !$apiKey) {
    fwrite(STDERR, "BILLOMAT_ID und BILLOMAT_API_KEY müssen gesetzt sein.\n");
    exit(1);
}

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

// 1) Kunden, sortiert nach Anlage absteigend
$recentClients = $billomat->clients->list(new ClientFilter(
    orderBy: OrderBy::desc('id'),
    perPage: 5,
));

echo "Zuletzt angelegte Kunden:\n";
foreach ($recentClients as $client) {
    printf("  #%d  %s\n", $client->id, $client->name);
}

// 2) Rechnungen, gefiltert nach mehreren Status-Werten
$openOrOverdue = $billomat->invoices->list(new InvoiceFilter(
    status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
    orderBy: OrderBy::desc('date')->thenAsc('invoice_number'),
    perPage: 10,
));

echo "\nOffene oder überfällige Rechnungen (max. 10):\n";
foreach ($openOrOverdue as $invoice) {
    printf("  #%d  %s  %s  offen: %.2f %s\n",
        $invoice->id,
        $invoice->invoiceNumber ?? '?',
        $invoice->status?->label() ?? '?',
        $invoice->openAmount ?? 0.0,
        $invoice->currencyCode ?? '',
    );
}
