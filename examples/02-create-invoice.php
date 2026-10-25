<?php

/**
 * Beispiel 02: Rechnung mit zwei Positionen in einem Call anlegen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt (ID eines bestehenden Kunden).
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/02-create-invoice.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;
$clientIdRaw = getenv('BILLOMAT_CLIENT_ID') ?: null;

if (!$billomatId || !$apiKey || !$clientIdRaw) {
    fwrite(STDERR, "BILLOMAT_ID, BILLOMAT_API_KEY und BILLOMAT_CLIENT_ID müssen gesetzt sein.\n");
    exit(1);
}

$clientId = (int)$clientIdRaw;

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

$opts = new InvoiceCreateOptions(
    clientId: $clientId,
    title: 'Beispiel-Rechnung ' . date('Y-m-d'),
    date: new DateTimeImmutable('today'),
    dueDays: 14,
    intro: 'vielen Dank für Ihren Auftrag.',
);

$item1 = new InvoiceItemCreateOptions(
    quantity: 5.0, unitPrice: 19.90,
    title: 'Demo-Artikel A',
    type: InvoiceItemType::PRODUCT,
    unit: 'Stück',
);
$opts = $opts->withItem($item1);

$item2 = new InvoiceItemCreateOptions(
    quantity: 1.5, unitPrice: 95.00,
    title: 'Demo-Dienstleistung',
    type: InvoiceItemType::SERVICE,
    unit: 'Stunde',
);
$opts = $opts->withItem($item2);

try {
    $invoice = $billomat->invoices->create($opts);
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Anlage fehlgeschlagen: %s\n", $e->getMessage()));
    exit(1);
}

printf(
    "Entwurf angelegt: #%d, Status %s, Brutto: %.2f %s\n",
    $invoice->id,
    $invoice->status?->label() ?? '?',
    $invoice->totalGross ?? 0.0,
    $invoice->currencyCode ?? '',
);
