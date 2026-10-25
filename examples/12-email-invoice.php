<?php

/**
 * Beispiel 12: Rechnung abschließen und per E-Mail an den Kunden versenden.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_INVOICE_ID gesetzt (ID einer DRAFT- oder OPEN-Rechnung).
 *  - BILLOMAT_EMAIL_TO gesetzt (Empfänger der Test-Mail).
 *  - Optional BILLOMAT_EMAIL_FROM (im Account verifizierte Absenderadresse).
 *
 * Aufruf:
 *  BILLOMAT_INVOICE_ID=98765 BILLOMAT_EMAIL_TO=du@example.com \
 *  php examples/12-email-invoice.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;

$invoiceId = envId('BILLOMAT_INVOICE_ID');
$emailTo = env('BILLOMAT_EMAIL_TO');
$billomat = billomat();

// 1) Einen Entwurf erst abschließen, sonst gibt es keine Nummer und kein PDF
$invoice = $billomat->invoices->getOrFail($invoiceId);

if (InvoiceStatus::DRAFT === $invoice->status) {
    $billomat->invoices->complete($invoiceId);
    $invoice = $billomat->invoices->getOrFail($invoiceId);
    printf("Rechnung abgeschlossen: %s\n", $invoice->invoiceNumber ?? '?');
}

$number = $invoice->invoiceNumber ?? (string) $invoiceId;

// 2) E-Mail versenden; nicht gesetzte Felder übernimmt Billomat aus der E-Mail-Vorlage
$billomat->invoices->email($invoiceId, new EmailOptions(
    from: optionalEnv('BILLOMAT_EMAIL_FROM'),
    to: [$emailTo],
    subject: sprintf('Ihre Rechnung %s', $number),
    body: "Sehr geehrte Damen und Herren,\n\nim Anhang finden Sie Ihre Rechnung. Bei Rückfragen melden Sie sich gerne.\n\nMit freundlichen Grüßen\n",
    filename: sprintf('rechnung-%s', $number),
));

printf("E-Mail an %s versendet.\n", $emailTo);
