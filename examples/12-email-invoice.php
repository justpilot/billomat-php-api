<?php

/**
 * Beispiel 12: Rechnung abschliessen und per E-Mail an den Kunden versenden.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID, BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_INVOICE_ID gesetzt (ID einer DRAFT- oder OPEN-Rechnung).
 *  - BILLOMAT_EMAIL_TO gesetzt (Ziel-Empfänger der Test-Mail).
 *  - Optional BILLOMAT_EMAIL_FROM (im Account verifizierte Absenderadresse).
 *
 * Aufruf:
 *  BILLOMAT_INVOICE_ID=98765 BILLOMAT_EMAIL_TO=du@example.com \
 *  php examples/12-email-invoice.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceEmailOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;
$invoiceIdRaw = getenv('BILLOMAT_INVOICE_ID') ?: null;
$emailTo = getenv('BILLOMAT_EMAIL_TO') ?: null;
$emailFrom = getenv('BILLOMAT_EMAIL_FROM') ?: null;

if (!$billomatId || !$apiKey || !$invoiceIdRaw || !$emailTo) {
    fwrite(STDERR, "BILLOMAT_ID, BILLOMAT_API_KEY, BILLOMAT_INVOICE_ID und BILLOMAT_EMAIL_TO müssen gesetzt sein.\n");
    exit(1);
}

$invoiceId = (int)$invoiceIdRaw;

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

try {
    // 1) Falls die Rechnung noch DRAFT ist, abschliessen — sonst gibt es kein PDF
    $invoice = $billomat->invoices->get($invoiceId);
    if ($invoice === null) {
        fwrite(STDERR, sprintf("Rechnung #%d nicht gefunden.\n", $invoiceId));
        exit(1);
    }

    if ($invoice->status === InvoiceStatus::DRAFT) {
        $billomat->invoices->complete($invoiceId);
        $invoice = $billomat->invoices->get($invoiceId);
        printf("Rechnung abgeschlossen: %s%d, Status %s\n",
            $invoice->numberPre ?? '',
            $invoice->number ?? 0,
            $invoice->status?->label() ?? '?',
        );
    }

    // 2) E-Mail-Optionen zusammenstellen
    $opts = new InvoiceEmailOptions();
    $opts->to = [$emailTo];
    if ($emailFrom !== null) {
        $opts->from = $emailFrom;
    }
    $opts->subject = sprintf('Ihre Rechnung %s%d', $invoice->numberPre ?? '', $invoice->number ?? 0);
    $opts->body = "Sehr geehrte Damen und Herren,\n\nim Anhang Ihre Rechnung. Bei Rückfragen melden Sie sich gerne.\n\nMit freundlichen Grüssen\n";
    $opts->filename = sprintf('rechnung-%s%d', $invoice->numberPre ?? '', $invoice->number ?? 0);

    $billomat->invoices->email($invoiceId, $opts);
    printf("E-Mail an %s versendet.\n", $emailTo);
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s\n", $e->getMessage()));
    exit(1);
}
