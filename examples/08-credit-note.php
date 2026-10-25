<?php

/**
 * Beispiel 08: Gutschrift anlegen, abschliessen und Auszahlung verbuchen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID, BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt.
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/08-credit-note.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;
$clientIdRaw = getenv('BILLOMAT_CLIENT_ID') ?: null;

if (!$billomatId || !$apiKey || !$clientIdRaw) {
    fwrite(STDERR, "BILLOMAT_ID, BILLOMAT_API_KEY und BILLOMAT_CLIENT_ID müssen gesetzt sein.\n");
    exit(1);
}

$clientId = (int)$clientIdRaw;

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

try {
    // 1) Gutschrift mit Position anlegen
    $opts = new CreditNoteCreateOptions(
        clientId: $clientId,
        title: 'Gutschrift ' . date('Y-m-d'),
        date: new DateTimeImmutable('today'),
        intro: 'Wir schreiben Ihnen folgenden Betrag gut:',
    );

    $item = new CreditNoteItemCreateOptions(
        quantity: 1.0, unitPrice: Money::of('75'),
        title: 'Rückerstattung Versand',
        type: InvoiceItemType::PRODUCT,
        unit: 'Pauschal',
    );
    $opts = $opts->withItem($item);

    $creditNote = $billomat->creditNotes->create($opts);
    printf("Entwurf angelegt: #%d, Brutto %.2f %s\n",
        $creditNote->id,
        $creditNote->totalGross ?? 0.0,
        $creditNote->currencyCode ?? '',
    );

    // 2) Abschliessen → Status OPEN
    $billomat->creditNotes->complete($creditNote->id);

    // 3) Auszahlung verbuchen → Status PAID
    $payment = new CreditNotePaymentCreateOptions(
        creditNoteId: $creditNote->id,
            amount: $creditNote->totalGross ?? 75.0,
        date: new DateTimeImmutable('today'),
        type: InvoicePaymentType::BANK_TRANSFER,
        markCreditNoteAsPaid: true,
        comment: 'Erstattung per SEPA-Überweisung',
    );

    $created = $billomat->creditNotes->payments->create($payment);
    printf("Auszahlung #%d verbucht: %.2f via %s\n",
        $created->id,
        $created->amount,
        $created->type?->label() ?? '?',
    );

    $final = $billomat->creditNotes->get($creditNote->id);
    printf("Final-Status: %s\n", $final->status?->value ?? '?');
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s\n", $e->getMessage()));
    exit(1);
}
