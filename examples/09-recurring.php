<?php

/**
 * Beispiel 09: Abo-Rechnung mit E-Mail-Empfänger und Tag anlegen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID, BILLOMAT_API_KEY gesetzt.
 *  - BILLOMAT_CLIENT_ID gesetzt.
 *
 * Aufruf:
 *  BILLOMAT_CLIENT_ID=12345 php examples/09-recurring.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Model\Enum\RecurringAction;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

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
    // 1) Monatliches Abo anlegen — Billomat erstellt + verschickt die Rechnung automatisch
    $opts = new RecurringCreateOptions(clientId: $clientId);
    $opts->name = 'Hosting Basic';
    $opts->title = 'Webhosting-Abo';
    $opts->cycle = RecurringCycle::MONTHLY;
    $opts->cycleNumber = 1;
    $opts->action = RecurringAction::EMAIL;
    $opts->startDate = new DateTimeImmutable('first day of next month');
    $opts->dueDays = 14;

    $item = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 19.9);
    $item->title = 'Webhosting Basic';
    $item->unit = 'Monat';
    $opts->addItem($item);

    $recurring = $billomat->recurrings->create($opts);
    printf("Abo #%d angelegt, nächster Lauf: %s\n",
        $recurring->id,
        $recurring->nextCreationDate?->format('Y-m-d') ?? 'tbd',
    );

    // 2) E-Mail-Empfänger pflegen — Pflicht, damit action=EMAIL nicht scheitert
    $billomat->recurringEmailReceivers->create(
        new RecurringEmailReceiverCreateOptions(
            recurringId: $recurring->id,
            type: RecurringEmailReceiverType::TO,
            address: 'kunde@example.com',
        ),
    );

    // 3) Schlagwort setzen
    $billomat->recurringTags->create(
        new RecurringTagCreateOptions(recurringId: $recurring->id, name: 'hosting'),
    );

    // 4) Preis-Update für die einzige Position
    foreach ($billomat->recurringItems->listByRecurring($recurring->id) as $current) {
        $update = new RecurringItemCreateOptions(quantity: 1.0, unitPrice: 24.9);
        $update->title = $current->title;
        $update->unit = $current->unit;
        $billomat->recurringItems->update($current->id, $update);
        printf("Position #%d auf %.2f angepasst.\n", $current->id, 24.9);
    }
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s\n", $e->getMessage()));
    exit(1);
}
