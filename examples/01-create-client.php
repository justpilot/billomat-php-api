<?php

/**
 * Beispiel 01: Einen Kunden anlegen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *
 * Aufruf:
 *  php examples/01-create-client.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\ClientCreateOptions;

$client = billomat()->clients->create(new ClientCreateOptions(
    name: sprintf('Beispiel GmbH %s', date('Ymd-His')),
    countryCode: 'DE',
    firstName: 'Max',
    lastName: 'Mustermann',
    salutation: 'Herr',
    email: 'kontakt@beispiel.example',
    note: 'Angelegt durch examples/01-create-client.php',
));

printf("Angelegt: #%d %s (E-Mail: %s)\n", $client->id, $client->name, $client->email ?? '-');
