<?php

/**
 * Beispiel 08: Gutschrift anlegen, abschließen und Auszahlung verbuchen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt (ID eines bestehenden Kunden).
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/08-credit-note.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Money;

$billomat = billomat();

// 1) Gutschrift mit einer Position anlegen
$creditNote = $billomat->creditNotes->create(
    new CreditNoteCreateOptions(
        clientId: envId('BILLOMAT_CLIENT_ID'),
        date: new DateTimeImmutable('today'),
        title: 'Gutschrift '.date('Y-m-d'),
        intro: 'Wir schreiben Ihnen folgenden Betrag gut:',
    )->withItem(new CreditNoteItemCreateOptions(
        quantity: 1.0,
        unitPrice: Money::of('75.00'),
        unit: 'Pauschal',
        title: 'Rückerstattung Versand',
        type: ItemType::PRODUCT,
    )),
);
$creditNoteId = $creditNote->id;
$amount = $creditNote->totalGross ?? Money::of('75.00');

printf("Entwurf angelegt: #%d, Brutto %s %s\n", $creditNoteId, $amount->format(2, ',', '.'), $creditNote->currencyCode ?? '');

// 2) Abschließen, Status wechselt auf OPEN
$billomat->creditNotes->complete($creditNoteId);

// 3) Auszahlung über den vollen Betrag verbuchen, Status wechselt auf PAID
$payment = $billomat->creditNotes->payments->create(new CreditNotePaymentCreateOptions(
    creditNoteId: $creditNoteId,
    amount: $amount,
    date: new DateTimeImmutable('today'),
    type: PaymentType::BANK_TRANSFER,
    comment: 'Erstattung per SEPA-Überweisung',
    markCreditNoteAsPaid: true,
));

printf("Auszahlung #%d verbucht: %s via %s\n", $payment->id, $payment->amount->format(2, ',', '.'), $payment->type?->label() ?? '?');
printf("Endstatus: %s\n", $billomat->creditNotes->getOrFail($creditNoteId)->status?->label() ?? '?');
