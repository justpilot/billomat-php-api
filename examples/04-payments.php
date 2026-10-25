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

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$invoiceId = envId('BILLOMAT_INVOICE_ID');
$payments = billomat()->invoices->payments;

// 1) Zahlung anlegen
$payment = $payments->create(new InvoicePaymentCreateOptions(
    invoiceId: $invoiceId,
    amount: Money::of('1.00'),
    date: new DateTimeImmutable('today'),
    type: PaymentType::BANK_TRANSFER,
    comment: 'Test-Zahlung aus examples/04-payments.php',
));
$paymentId = $payment->id;

printf("Zahlung #%d angelegt: %s via %s\n", $paymentId, $payment->amount->format(2, ',', '.'), $payment->type?->label() ?? '?');

// 2) Alle Zahlungen der Rechnung listen, neueste zuerst
$all = $payments->list($invoiceId, ['order_by' => OrderBy::desc('date')->toQuery()]);

printf("Zahlungen insgesamt: %d\n", count($all));
foreach ($all as $p) {
    printf(
        "  - #%d  %s  %s  %s\n",
        $p->id,
        $p->date?->format('Y-m-d') ?? '?',
        $p->amount->format(2, ',', '.'),
        $p->type?->label() ?? '?',
    );
}

// 3) Test-Zahlung wieder entfernen
$payments->delete($paymentId);
printf("Test-Zahlung #%d wieder gelöscht.\n", $paymentId);
