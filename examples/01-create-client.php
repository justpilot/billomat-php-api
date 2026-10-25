<?php

/**
 * Beispiel 01: Einen Kunden anlegen.
 *
 * Voraussetzungen:
 *  - Umgebungsvariablen BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *
 * Aufruf:
 *  php examples/01-create-client.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;

if (!$billomatId || !$apiKey) {
    fwrite(STDERR, "BILLOMAT_ID und BILLOMAT_API_KEY müssen gesetzt sein.\n");
    exit(1);
}

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

$opts = new ClientCreateOptions(
    name: sprintf('Beispiel GmbH %s', date('Ymd-His')),
    countryCode: 'DE',
    email: 'kontakt@beispiel.example',
    salutation: 'Herr',
    firstName: 'Max',
    lastName: 'Mustermann',
    note: 'Angelegt durch examples/01-create-client.php',
);

try {
    $client = $billomat->clients->create($opts);
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Anlage fehlgeschlagen: %s\n", $e->getMessage()));
    exit(1);
}

printf("Angelegt: #%d %s (E-Mail: %s)\n", $client->id, $client->name, $client->email ?? '-');
