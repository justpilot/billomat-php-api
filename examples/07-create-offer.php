<?php

/**
 * Beispiel 07: Angebot anlegen, abschließen und als gewonnen markieren.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt (ID eines bestehenden Kunden).
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/07-create-offer.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$billomat = billomat();

// 1) Angebot mit zwei Positionen anlegen (DRAFT)
$offer = $billomat->offers->create(
    new OfferCreateOptions(
        clientId: envId('BILLOMAT_CLIENT_ID'),
        date: new DateTimeImmutable('today'),
        validityDays: 14,
        title: 'Angebot '.date('Y-m-d'),
        intro: 'Vielen Dank für Ihre Anfrage. Hier unser unverbindliches Angebot:',
    )
        ->withItem(new OfferItemCreateOptions(
            quantity: 1.0,
            unitPrice: Money::of('980.00'),
            unit: 'Tag',
            title: 'Workshop "Discovery"',
            type: ItemType::SERVICE,
        ))
        ->withItem(new OfferItemCreateOptions(
            quantity: 4.0,
            unitPrice: Money::of('110.00'),
            unit: 'Stunde',
            title: 'Nachgespräche & Reporting',
            type: ItemType::SERVICE,
        )),
);
$offerId = requireId($offer->id);

printf(
    "Entwurf angelegt: #%d, Status %s, Brutto %s %s\n",
    $offerId,
    $offer->status?->label() ?? '?',
    $offer->totalGross?->format(2, ',', '.') ?? '?',
    $offer->currencyCode ?? '',
);

// 2) Abschließen vergibt die Angebotsnummer, Status wechselt auf OPEN
$billomat->offers->complete($offerId);
$opened = $billomat->offers->getOrFail($offerId);
printf("Abgeschlossen: %s, Status %s\n", $opened->offerNumber ?? '?', $opened->status?->label() ?? '?');

// 3) Als gewonnen markieren
$billomat->offers->win($offerId);
printf("Endstatus: %s\n", $billomat->offers->getOrFail($offerId)->status?->label() ?? '?');
