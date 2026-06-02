<?php

/**
 * Beispiel 10: Eingangsrechnung erfassen, taggen und Zahlung verbuchen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID, BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_SUPPLIER_ID gesetzt (ID eines bestehenden Lieferanten).
 *
 * Aufruf:
 *  BILLOMAT_SUPPLIER_ID=98765 php examples/10-incoming.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;
$supplierIdRaw = getenv('BILLOMAT_SUPPLIER_ID') ?: null;

if (!$billomatId || !$apiKey || !$supplierIdRaw) {
    fwrite(STDERR, "BILLOMAT_ID, BILLOMAT_API_KEY und BILLOMAT_SUPPLIER_ID müssen gesetzt sein.\n");
    exit(1);
}

$supplierId = (int)$supplierIdRaw;

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

try {
    // 1) Eingangsrechnung anlegen
    $opts = new IncomingCreateOptions(supplierId: $supplierId);
    $opts->incomingNumber = 'RE-' . date('Ymd-His');
    $opts->date = new DateTimeImmutable('yesterday');
    $opts->supplyDate = new DateTimeImmutable('yesterday');
    $opts->dueDays = 14;
    $opts->totalGross = 119.0;
    $opts->totalNet = 100.0;
    $opts->note = 'Beleg-Erfassung aus examples/10-incoming.php';

    $incoming = $billomat->incomings->create($opts);
    printf("Eingangsbeleg #%d angelegt: Brutto %.2f %s\n",
        $incoming->id,
        $incoming->totalGross ?? 0.0,
        $incoming->currencyCode ?? '',
    );

    // 2) Schlagwort anhängen — z. B. "büro"
    $billomat->incomingTags->create(
        new IncomingTagCreateOptions(incomingId: $incoming->id, name: 'büro'),
    );

    // 3) Zahlung verbuchen → markiert direkt als bezahlt
    $payment = new IncomingPaymentCreateOptions(
        incomingId: $incoming->id,
        amount: $incoming->totalGross ?? 119.0,
    );
    $payment->date = new DateTimeImmutable('today');
    $payment->type = InvoicePaymentType::BANK_TRANSFER;
    $payment->markIncomingAsPaid = true;
    $payment->transactionPurpose = $incoming->incomingNumber ?? '';

    $created = $billomat->incomingPayments->create($payment);
    printf("Zahlung #%d verbucht: %.2f via %s\n",
        $created->id,
        $created->amount,
        $created->type?->label() ?? '?',
    );
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s\n", $e->getMessage()));
    exit(1);
}
