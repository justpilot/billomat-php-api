<?php

/**
 * Beispiel 07: Angebot anlegen, abschliessen und als gewonnen markieren.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID, BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt (ID eines bestehenden Kunden).
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/07-create-offer.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;

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
    // 1) Angebot mit zwei Positionen anlegen (DRAFT)
    $opts = new OfferCreateOptions(
        clientId: $clientId,
        title: 'Angebot ' . date('Y-m-d'),
        date: new DateTimeImmutable('today'),
        validityDays: 14,
        intro: 'Vielen Dank für Ihre Anfrage. Hier unser unverbindliches Angebot:',
    );

    $workshop = new OfferItemCreateOptions(
        quantity: 1.0, unitPrice: Money::of('980'),
        title: 'Workshop "Discovery"',
        type: InvoiceItemType::SERVICE,
        unit: 'Tag',
    );
    $opts = $opts->withItem($workshop);

    $followUp = new OfferItemCreateOptions(
        quantity: 4.0, unitPrice: Money::of('110'),
        title: 'Nachgespräche & Reporting',
        type: InvoiceItemType::SERVICE,
        unit: 'Stunde',
    );
    $opts = $opts->withItem($followUp);

    $offer = $billomat->offers->create($opts);
    printf("Entwurf angelegt: #%d, Status %s, Brutto %.2f %s\n",
        $offer->id,
        $offer->status?->value ?? '?',
        $offer->totalGross ?? 0.0,
        $offer->currencyCode ?? '',
    );

    // 2) Abschliessen → vergibt Angebotsnummer, Status wechselt nach OPEN
    $billomat->offers->complete($offer->id);
    $opened = $billomat->offers->get($offer->id);
    printf("Abgeschlossen: %s%s — Status %s\n",
        $opened->numberPre ?? '',
        $opened->number ?? '?',
        $opened->status?->value ?? '?',
    );

    // 3) Als gewonnen markieren
    $billomat->offers->win($offer->id);
    $won = $billomat->offers->get($offer->id);
    printf("Final-Status: %s\n", $won->status?->value ?? '?');
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s\n", $e->getMessage()));
    exit(1);
}
