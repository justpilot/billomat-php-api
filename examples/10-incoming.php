<?php

/**
 * Beispiel 10: Eingangsrechnung erfassen, taggen und Zahlung verbuchen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_SUPPLIER_ID gesetzt (ID eines bestehenden Lieferanten).
 *
 * Aufruf:
 *  BILLOMAT_SUPPLIER_ID=98765 php examples/10-incoming.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingTagCreateOptions;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$billomat = billomat();

// 1) Eingangsrechnung anlegen
$incoming = $billomat->incomings->create(new IncomingCreateOptions(
    supplierId: envId('BILLOMAT_SUPPLIER_ID'),
    date: new DateTimeImmutable('yesterday'),
    supplyDate: new DateTimeImmutable('yesterday'),
    dueDays: 14,
    incomingNumber: 'RE-'.date('Ymd-His'),
    note: 'Beleg-Erfassung aus examples/10-incoming.php',
    totalGross: Money::of('119.00'),
    totalNet: Money::of('100.00'),
));
$incomingId = requireId($incoming->id);
$amount = $incoming->totalGross ?? Money::of('119.00');

printf("Eingangsbeleg #%d angelegt: Brutto %s %s\n", $incomingId, $amount->format(2, ',', '.'), $incoming->currencyCode ?? '');

// 2) Schlagwort anhängen
$billomat->incomings->tags->create(new IncomingTagCreateOptions(incomingId: $incomingId, name: 'büro'));

// 3) Zahlung verbuchen und den Beleg direkt als bezahlt markieren
$payment = $billomat->incomings->payments->create(new IncomingPaymentCreateOptions(
    incomingId: $incomingId,
    amount: $amount,
    date: new DateTimeImmutable('today'),
    type: PaymentType::BANK_TRANSFER,
    transactionPurpose: $incoming->incomingNumber,
    markIncomingAsPaid: true,
));

printf("Zahlung #%d verbucht: %s via %s\n", requireId($payment->id), $payment->amount->format(2, ',', '.'), $payment->type?->label() ?? '?');
