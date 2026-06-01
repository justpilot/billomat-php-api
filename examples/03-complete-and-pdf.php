<?php

/**
 * Beispiel 03: Rechnung abschließen und PDF lokal speichern.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_INVOICE_ID gesetzt (ID einer DRAFT-Rechnung; siehe Beispiel 02).
 *
 * Aufruf:
 *  BILLOMAT_INVOICE_ID=98765 php examples/03-complete-and-pdf.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;
$invoiceIdRaw = getenv('BILLOMAT_INVOICE_ID') ?: null;

if (!$billomatId || !$apiKey || !$invoiceIdRaw) {
    fwrite(STDERR, "BILLOMAT_ID, BILLOMAT_API_KEY und BILLOMAT_INVOICE_ID müssen gesetzt sein.\n");
    exit(1);
}

$invoiceId = (int)$invoiceIdRaw;

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

try {
    $billomat->invoices->complete($invoiceId);

    $invoice = $billomat->invoices->get($invoiceId);
    if ($invoice === null) {
        fwrite(STDERR, "Rechnung nach complete() nicht mehr auffindbar.\n");
        exit(1);
    }

    printf(
        "Abgeschlossen: %s%s — Status %s\n",
        $invoice->numberPre ?? '',
        $invoice->number ?? '?',
        $invoice->status?->label() ?? '?',
    );

    $pdfBinary = $billomat->invoices->pdf($invoiceId, InvoicePdfType::SIGNED, rawPdf: true);
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s\n", $e->getMessage()));
    exit(1);
}

$outputFile = sprintf('rechnung-%d.pdf', $invoiceId);
$bytes = file_put_contents($outputFile, $pdfBinary);

if ($bytes === false) {
    fwrite(STDERR, "Konnte PDF nicht schreiben.\n");
    exit(1);
}

printf("PDF gespeichert: %s (%d Bytes)\n", $outputFile, $bytes);
