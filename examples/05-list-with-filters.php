<?php

/**
 * Beispiel 05: Filter, Pagination und Sortierung beim Listen.
 *
 * Demonstriert die Billomat-Eigenheiten:
 *  - "+DESC" / "+ASC" im order_by-Parameter (das "+" bleibt literal,
 *    nicht "%2B" — siehe docs/advanced/http-layer.md)
 *  - Array-Filter (z. B. status[]=OPEN&status[]=OVERDUE)
 *
 * Aufruf:
 *  php examples/05-list-with-filters.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;

if (!$billomatId || !$apiKey) {
    fwrite(STDERR, "BILLOMAT_ID und BILLOMAT_API_KEY müssen gesetzt sein.\n");
    exit(1);
}

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

// 1) Kunden, sortiert nach Anlage absteigend
$recentClients = $billomat->clients->list([
    'per_page' => 5,
    'order_by' => 'id+DESC',
]);

echo "Zuletzt angelegte Kunden:\n";
foreach ($recentClients as $client) {
    printf("  #%d  %s\n", $client->id, $client->name);
}

// 2) Rechnungen, gefiltert nach mehreren Status-Werten
$openOrOverdue = $billomat->invoices->list([
    'status' => ['OPEN', 'OVERDUE'],
    'order_by' => 'date+DESC',
    'per_page' => 10,
]);

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
