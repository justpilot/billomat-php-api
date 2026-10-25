<?php

/**
 * Beispiel 06: Fehlerbehandlung in der Praxis.
 *
 * Demonstriert:
 *  - get($id) liefert bei 404 null, getOrFail($id) wirft NotFoundException
 *  - ValidationException mit lesbarem Roh-Body
 *  - AuthenticationException erkennen (über bewusst falschen Key)
 *
 * Aufruf:
 *  php examples/06-error-handling.php
 */

declare(strict_types=1);

use Justpilot\Billomat\Model\Client;

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;

$billomat = billomat();

// 1) get() liefert null bei 404, getOrFail() wirft
$missing = $billomat->clients->get(99999999);
echo '1) get(): '.($missing instanceof Client ? "Treffer?!\n" : "null wie erwartet\n");

try {
    (void) $billomat->clients->getOrFail(99999999);
    echo "1) getOrFail(): NotFoundException erwartet.\n";
} catch (NotFoundException $e) {
    printf("1) getOrFail(): NotFoundException %d\n", $e->getStatusCode());
}

// 2) ValidationException: leeren Kundennamen senden
try {
    $billomat->clients->create(new ClientCreateOptions(name: ''));
    echo "2) ValidationException erwartet, aber kein Fehler.\n";
} catch (ValidationException $e) {
    printf("2) ValidationException %d: %s\n", $e->getStatusCode(), substr($e->getResponseBody() ?? '', 0, 200));
}

// 3) AuthenticationException: bewusst falschen Key benutzen
$broken = BillomatClient::create(billomatId: env('BILLOMAT_ID'), apiKey: 'definitiv-falscher-key');

try {
    (void) $broken->clients->list(new ClientFilter(perPage: 1));
    echo "3) AuthenticationException erwartet, aber kein Fehler.\n";
} catch (AuthenticationException $e) {
    printf("3) AuthenticationException %d, wie erwartet.\n", $e->getStatusCode());
}
