<?php

/**
 * Beispiel 03: Rechnung abschließen und PDF lokal speichern.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_INVOICE_ID gesetzt (ID einer DRAFT-Rechnung, siehe Beispiel 02).
 *
 * Aufruf:
 *  BILLOMAT_INVOICE_ID=98765 php examples/03-complete-and-pdf.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Model\Enum\PdfType;

$invoiceId = envId('BILLOMAT_INVOICE_ID');
$billomat = billomat();

// Abschließen vergibt die Rechnungsnummer, der Status wechselt auf OPEN
$billomat->invoices->complete($invoiceId);
$invoice = $billomat->invoices->getOrFail($invoiceId);

printf("Abgeschlossen: %s, Status %s\n", $invoice->invoiceNumber ?? '?', $invoice->status?->label() ?? '?');

// pdfContent() liefert den binären PDF-Inhalt, pdf() das JSON-Modell mit base64-Inhalt
$pdf = $billomat->invoices->pdfContent($invoiceId, PdfType::SIGNED);

$outputFile = sprintf('rechnung-%d.pdf', $invoiceId);
$bytes = file_put_contents($outputFile, $pdf);

if (false === $bytes) {
    fwrite(\STDERR, "Konnte PDF nicht schreiben.\n");
    exit(1);
}

printf("PDF gespeichert: %s (%d Bytes)\n", $outputFile, $bytes);
