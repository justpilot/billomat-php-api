<?php

/**
 * Beispiel 04: Zahlung anlegen, listen und wieder löschen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_INVOICE_ID gesetzt (ID einer abgeschlossenen Rechnung).
 *
 * Aufruf:
 *  BILLOMAT_INVOICE_ID=98765 php examples/04-payments.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

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
    // Zahlung anlegen
    $opts = new InvoicePaymentCreateOptions(invoiceId: $invoiceId, amount: 1.00);
    $opts->date = new DateTimeImmutable('today');
    $opts->type = InvoicePaymentType::BANK_TRANSFER;
    $opts->comment = 'Test-Zahlung aus examples/04-payments.php';

    $payment = $billomat->invoicePayments->create($opts);
    printf("Zahlung #%d angelegt: %.2f EUR via %s\n",
        $payment->id,
        $payment->amount,
        $payment->type?->label() ?? '?',
    );

    // Alle Zahlungen zur Rechnung listen
    $all = $billomat->invoicePayments->list([
        'invoice_id' => $invoiceId,
        'order_by' => 'date+DESC',
    ]);
    printf("Zahlungen insgesamt: %d\n", count($all));
    foreach ($all as $p) {
        printf("  - #%d  %s  %.2f EUR  %s\n",
            $p->id,
            $p->date?->format('Y-m-d') ?? '?',
            $p->amount,
            $p->type?->label() ?? '?',
        );
    }

    // Die eben angelegte Test-Zahlung wieder entfernen
    $billomat->invoicePayments->delete($payment->id);
    printf("Test-Zahlung #%d wieder gelöscht.\n", $payment->id);
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s (HTTP %s)\n",
        $e->getMessage(),
        method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 'n/a',
    ));
    exit(1);
}
