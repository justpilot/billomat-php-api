<?php

declare(strict_types=1);

/*
 * Smoke-Test: Das SDK muss ohne Symfony mit Guzzle als einzigem PSR-18-Client laufen.
 *
 * Ausführen: composer install && php smoke.php (in diesem Verzeichnis).
 */

require __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    echo "ok: {$message}\n";
}

check(!class_exists(Symfony\Component\HttpClient\HttpClient::class), 'symfony/http-client is not installed');

$mock = new MockHandler([
    new Response(503),
    new Response(200, [], '{"clients":{"client":{"id":1,"name":"ACME"}}}'),
    new Response(422, [], '{"errors":{"error":"Name is missing"}}'),
]);

$billomat = BillomatClient::create(
    billomatId: 'demo',
    apiKey: 'secret',
    httpClient: new Client(['handler' => HandlerStack::create($mock)]),
    retry: new RetryPolicy(baseDelayMs: 1),
);

$clients = $billomat->clients->list();
check('ACME' === $clients[0]->name, 'GET is retried after 503 and hydrated');

$request = $mock->getLastRequest();
check(null !== $request && 'secret' === $request->getHeaderLine('X-BillomatApiKey'), 'API key header is sent');

try {
    $billomat->getHttpClient()->request('POST', '/clients', [], ['client' => []]);
    check(false, '422 throws');
} catch (ValidationException $e) {
    check(str_ends_with($e->getMessage(), 'Name is missing'), '422 maps to ValidationException with Billomat error text');
}

$client = new ReflectionProperty(BillomatHttpClient::class, 'client')
    ->getValue(BillomatClient::create(billomatId: 'demo', apiKey: 'secret')->getHttpClient());
check($client instanceof Client, 'php-http/discovery finds Guzzle as default client');
