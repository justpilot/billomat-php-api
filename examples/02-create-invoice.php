<?php

/**
 * Beispiel 02: Rechnung mit zwei Positionen in einem Call anlegen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt (ID eines bestehenden Kunden).
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/02-create-invoice.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Money;

$options = new InvoiceCreateOptions(
    clientId: envId('BILLOMAT_CLIENT_ID'),
    date: new DateTimeImmutable('today'),
    dueDays: 14,
    title: 'Beispiel-Rechnung '.date('Y-m-d'),
    intro: 'vielen Dank für Ihren Auftrag.',
)
    ->withItem(new InvoiceItemCreateOptions(
        quantity: 5.0,
        unitPrice: Money::of('19.90'),
        unit: 'Stück',
        title: 'Demo-Artikel A',
        type: ItemType::PRODUCT,
    ))
    ->withItem(new InvoiceItemCreateOptions(
        quantity: 1.5,
        unitPrice: Money::of('95.00'),
        unit: 'Stunde',
        title: 'Demo-Dienstleistung',
        type: ItemType::SERVICE,
    ));

$invoice = billomat()->invoices->create($options);

printf(
    "Entwurf angelegt: #%d, Status %s, Brutto %s %s\n",
    requireId($invoice->id),
    $invoice->status?->label() ?? '?',
    $invoice->totalGross?->format(2, ',', '.') ?? '?',
    $invoice->currencyCode ?? '',
);
