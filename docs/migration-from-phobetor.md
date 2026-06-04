# Migrating from `phobetor/billomat` or `vrok/billomat-client`

This guide is for users moving an existing application from
[`phobetor/billomat`](https://packagist.org/packages/phobetor/billomat) or its
successor [`vrok/billomat-client`](https://packagist.org/packages/vrok/billomat-client)
(also published as `j-schumann/billomat-client`) to `justpilot/billomat-php-api`.

Both legacy packages share a CRUD-style API on a single `BillomatClient` and
return plain arrays. This SDK uses one `*Api` class per resource and returns
`final readonly` model instances. The translation is mostly mechanical.

## Before you start

- **PHP requirement.** This SDK requires PHP 8.4+. Check `php -v` and your CI
  matrix before migrating. If you cannot upgrade, this SDK is not for you.
- **Composer.** You can install both side by side during the migration —
  `Phobetor\Billomat\Client\BillomatClient` and
  `Justpilot\Billomat\BillomatClient` live in separate namespaces and do not
  conflict.

```bash
composer require justpilot/billomat-php-api
# Once migrated:
composer remove vrok/billomat-client    # or: phobetor/billomat
```

## Instantiation

```php
// Before (phobetor/vrok)
use Phobetor\Billomat\Client\BillomatClient;

$billomat = new BillomatClient('mycompany', 'api-key', 'app-id', 'app-secret');
```

```php
// After (justpilot)
use Justpilot\Billomat\BillomatClient;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: 'api-key',
    appId: 'app-id',
    appSecret: 'app-secret',
);
```

The first positional argument (`my-id` / `billomatId`) is identical — it is
your Billomat subdomain.

## Resource access

phobetor/vrok flatten every method onto the root client
(`$billomat->getClient(…)`, `$billomat->createInvoice(…)`). This SDK groups
methods by resource (`$billomat->clients->get(…)`, `$billomat->invoices->create(…)`).

| Concern | phobetor/vrok | justpilot |
|---|---|---|
| Client surface | one flat `BillomatClient` with `~120` methods | one `*Api` per resource, reached via readonly property |
| Method name | `get{Resource}` / `create{Resource}` | `list` / `get` / `create` / `update` / `delete` |
| Argument style | single associative array | typed scalars + `*CreateOptions` / `*UpdateOptions` |
| Return type | `array` (associative) | `final readonly` model with typed properties |

## Side-by-side: common operations

### List

```php
// Before
$clients = $billomat->getClients();
foreach ($clients as $client) {
    echo $client['name'];
}
```

```php
// After
$clients = $billomat->clients->list();
foreach ($clients as $client) {
    echo $client->name;
}
```

### List with filters

```php
// Before
$clients = $billomat->getClients(['country_code' => 'DE', 'per_page' => 100]);
```

```php
// After
$clients = $billomat->clients->list(['country_code' => 'DE', 'per_page' => 100]);
```

### List across pages

```php
// Before — pagination by hand
$page = 1;
do {
    $batch = $billomat->getClients(['per_page' => 100, 'page' => $page]);
    foreach ($batch as $client) { /* … */ }
    $page++;
} while (count($batch) === 100);
```

```php
// After — lazy generator, stops at the last page automatically
foreach ($billomat->clients->iterateAll() as $client) {
    /* … */
}

// Or one page with metadata
$result = $billomat->clients->listPage(['per_page' => 50, 'page' => 3]);
echo "Page {$result->info->page} of " . ($result->info->totalPages() ?? '?');
```

### Get one (404 handling)

```php
// Before — throws on 404
try {
    $client = $billomat->getClient(['id' => 12345]);
} catch (\GuzzleHttp\Command\Exception\CommandException $e) {
    if ($e->getPrevious() instanceof \Phobetor\Billomat\Exception\NotFoundException) {
        $client = null;
    } else {
        throw $e;
    }
}
```

```php
// After — get() returns null on 404
$client = $billomat->clients->get(12345); // ?Client
```

### Create

```php
// Before
$result = $billomat->createClient([
    'client' => [
        'number'      => 424242,
        'name'        => 'Acme Inc.',
        'country_code'=> 'DE',
    ],
]);
$id = $result['id'];
```

```php
// After
use Justpilot\Billomat\Api\ClientCreateOptions;

$options = new ClientCreateOptions();
$options->name = 'Acme Inc.';
$options->number = 424242;
$options->countryCode = 'DE';

$client = $billomat->clients->create($options);
$id = $client->id;
```

Some `*CreateOptions` classes (e.g. `InvoiceCreateOptions`, `InvoiceItemCreateOptions`)
do take required arguments via the constructor — see [README](../README.en.md#quickstart)
and the [examples/](../examples/) folder for the per-resource form. `ClientCreateOptions`
is the property-only variant because Billomat treats every client field as optional.

### Update

```php
// Before
$billomat->updateClient([
    'id'     => 12345,
    'client' => ['name' => 'Acme GmbH'],
]);
```

```php
// After
use Justpilot\Billomat\Api\ClientUpdateOptions;

$options = new ClientUpdateOptions();
$options->name = 'Acme GmbH';

$billomat->clients->update(12345, $options);
```

### Delete

```php
// Before
$billomat->deleteClient(['id' => 12345]);
```

```php
// After
$billomat->clients->delete(12345);
```

### Complete an invoice and download the PDF

```php
// Before
$billomat->completeInvoice(['id' => $invoiceId]);
$response = $billomat->getInvoicePdf(['id' => $invoiceId, 'format' => 'pdf']);
file_put_contents("invoice-{$invoiceId}.pdf", (string) $response->getBody());
```

```php
// After
use Justpilot\Billomat\Model\Enum\InvoicePdfType;

$billomat->invoices->complete($invoiceId);
$pdf = $billomat->invoices->pdf($invoiceId, InvoicePdfType::SIGNED, rawPdf: true);
file_put_contents("invoice-{$invoiceId}.pdf", $pdf);
```

## Exception handling

phobetor/vrok wrap each error in a Guzzle `CommandException` whose previous
exception is the actual SDK exception. This SDK throws the SDK exception
directly.

```php
// Before
try {
    $billomat->updateClient(['id' => 12345, 'client' => ['name' => '']]);
} catch (\GuzzleHttp\Command\Exception\CommandException $e) {
    $prev = $e->getPrevious();
    if ($prev instanceof \Phobetor\Billomat\Exception\NotFoundException) { /* 404 */ }
    elseif ($prev instanceof \Phobetor\Billomat\Exception\BadRequestException) { /* 400/422 */ }
    elseif ($prev instanceof \Phobetor\Billomat\Exception\TooManyRequestsException) { /* 429 */ }
    else { throw $e; }
}
```

```php
// After
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Exception\HttpException;

try {
    $billomat->clients->update(12345, $options);
} catch (NotFoundException $e) {           // 404
} catch (ValidationException $e) {         // 400, 422
} catch (AuthenticationException $e) {     // 401, 403
} catch (HttpException $e) {               // other 4xx/5xx, incl. 429
    if ($e->getStatusCode() === 429) {
        // rate limit; inspect $e->getResponseBody() for details
    }
}
```

This SDK does not yet ship an automatic rate-limit wait loop equivalent to
phobetor's `setDoWaitForRateLimitReset(true)`. If you need it, decorate the
HTTP client (see the [Logging & HTTP client](../README.en.md#logging--http-client)
section).

## Property name mapping

phobetor/vrok pass through Billomat's snake_case field names verbatim
(`country_code`, `first_name`, `currency_code`, `due_days`). The read models
and write options in this SDK use camelCase (`countryCode`, `firstName`,
`currencyCode`, `dueDays`). The mapping is mechanical and consistent across
every resource.

## Where to go next

- Resource overview: [README.en.md](../README.en.md#resources)
- Pagination details: [docs/advanced/pagination.md](advanced/pagination.md)
- Error handling: [docs/error-handling.md](error-handling.md)
- HTTP layer internals (query encoding, retry decoration): [docs/advanced/http-layer.md](advanced/http-layer.md)
- Runnable examples: [examples/](../examples/)
