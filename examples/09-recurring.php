<?php

/**
 * Beispiel 09: Abo-Rechnung mit E-Mail-Empfänger, Tag und Preis-Update.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt (ID eines bestehenden Kunden).
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/09-recurring.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringItemUpdateOptions;
use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Model\Money;

$billomat = billomat();

// 1) Monatliches Abo anlegen: Billomat erstellt und verschickt die Rechnung automatisch
$recurring = $billomat->recurrings->create(
    new RecurringCreateOptions(
        clientId: envId('BILLOMAT_CLIENT_ID'),
        dueDays: 14,
        name: 'Hosting Basic',
        title: 'Webhosting-Abo',
        action: RecurringAction::EMAIL,
        cycleNumber: 1,
        cycle: RecurringCycle::MONTHLY,
        startDate: new DateTimeImmutable('first day of next month'),
    )->withItem(new RecurringItemCreateOptions(
        quantity: 1.0,
        unitPrice: Money::of('19.90'),
        unit: 'Monat',
        title: 'Webhosting Basic',
    )),
);
$recurringId = $recurring->id;

printf("Abo #%d angelegt, nächster Lauf: %s\n", $recurringId, $recurring->nextCreationDate?->format('Y-m-d') ?? 'offen');

// 2) E-Mail-Empfänger hinterlegen, sonst scheitert action=EMAIL
$billomat->recurrings->emailReceivers->create($recurringId, new RecurringEmailReceiverCreateOptions(
    type: RecurringEmailReceiverType::TO,
    address: 'kunde@example.com',
));

// 3) Schlagwort setzen
$billomat->recurrings->tags->create($recurringId, new RecurringTagCreateOptions(name: 'hosting'));

// 4) Preis der Positionen anheben: nur der Preis wird gesendet, die übrigen Felder bleiben
$newPrice = Money::of('24.90');
foreach ($billomat->recurrings->items->list($recurringId) as $item) {
    $billomat->recurrings->items->update($item->id, new RecurringItemUpdateOptions(unitPrice: $newPrice));
    printf("Position #%d auf %s angepasst.\n", $item->id, $newPrice->format(2, ',', '.'));
}
