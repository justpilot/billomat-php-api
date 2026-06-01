<?php

/**
 * Beispiel 06: Fehlerbehandlung in der Praxis.
 *
 * Demonstriert:
 *  - NotFoundException-Verhalten von get($id) (gibt null zurück, wirft nicht)
 *  - ValidationException mit lesbarem Roh-Body
 *  - AuthenticationException erkennen (über bewusst falschen Key)
 *
 * Aufruf:
 *  php examples/06-error-handling.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\ValidationException;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;

if (!$billomatId || !$apiKey) {
    fwrite(STDERR, "BILLOMAT_ID und BILLOMAT_API_KEY müssen gesetzt sein.\n");
    exit(1);
}

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

// 1) get() liefert null bei 404 — kein Try/Catch nötig
$missing = $billomat->clients->get(99999999);
echo "1) Nicht existierender Kunde: " . ($missing === null ? "null wie erwartet\n" : "Treffer?!\n");

// 2) ValidationException: leeren Client-Namen senden
$badOpts = new ClientCreateOptions();
$badOpts->name = '';

try {
    $billomat->clients->create($badOpts);
    echo "2) Validierungsfehler erwartet, aber kein Fehler geflogen.\n";
} catch (ValidationException $e) {
    printf("2) ValidationException %d: %s\n", $e->getStatusCode(), substr((string)$e->getResponseBody(), 0, 200));
}

// 3) AuthenticationException: bewusst falschen Key benutzen
$brokenBillomat = BillomatClient::create(
    billomatId: $billomatId,
    apiKey: 'definitiv-falscher-key',
);

try {
    $brokenBillomat->clients->list(['per_page' => 1]);
    echo "3) Auth-Fehler erwartet, aber kein Fehler geflogen.\n";
} catch (AuthenticationException $e) {
    printf("3) AuthenticationException %d — wie erwartet.\n", $e->getStatusCode());
}
