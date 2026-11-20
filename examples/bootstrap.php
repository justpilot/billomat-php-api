<?php

/**
 * Gemeinsamer Einstieg für alle Beispiele: Autoloader, Umgebungsvariablen,
 * Client und eine kurze Fehlerausgabe statt Stacktrace.
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException;

set_exception_handler(static function (Throwable $e): void {
    $status = $e instanceof HttpException ? sprintf(' (HTTP %d)', $e->getStatusCode()) : '';
    $prefix = $e instanceof BillomatException ? 'Billomat-Fehler' : 'Fehler';
    fwrite(\STDERR, sprintf("%s%s: %s\n", $prefix, $status, $e->getMessage()));
    exit(1);
});

/** Liest eine optionale Umgebungsvariable; leer zählt als nicht gesetzt. */
function optionalEnv(string $name): ?string
{
    $value = getenv($name);

    return is_string($value) && '' !== $value ? $value : null;
}

/** Liest eine Pflicht-Umgebungsvariable oder bricht mit Hinweis ab. */
function env(string $name): string
{
    $value = optionalEnv($name);

    if (null === $value) {
        fwrite(\STDERR, sprintf("Umgebungsvariable %s muss gesetzt sein.\n", $name));
        exit(1);
    }

    return $value;
}

/** Liest eine Pflicht-Umgebungsvariable mit einer positiven Ganzzahl (z. B. eine ID). */
function envId(string $name): int
{
    $value = env($name);

    if (1 !== preg_match('/^[1-9]\d*$/', $value)) {
        fwrite(\STDERR, sprintf("%s muss eine positive Ganzzahl sein, \"%s\" erhalten.\n", $name, $value));
        exit(1);
    }

    return (int) $value;
}

/** Baut den Client aus BILLOMAT_ID, BILLOMAT_API_KEY und optional BILLOMAT_APP_ID/BILLOMAT_APP_SECRET. */
function billomat(): BillomatClient
{
    return BillomatClient::create(
        billomatId: env('BILLOMAT_ID'),
        apiKey: env('BILLOMAT_API_KEY'),
        appId: optionalEnv('BILLOMAT_APP_ID'),
        appSecret: optionalEnv('BILLOMAT_APP_SECRET'),
    );
}
