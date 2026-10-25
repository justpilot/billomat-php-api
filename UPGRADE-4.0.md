# Upgrading from 3.x to 4.0

This guide lists every backward-incompatible change in 4.0 with a before/after example.
It grows with each 4.0 pre-release; the [CHANGELOG](CHANGELOG.md) has the full list of changes.

Recommended order:

1. Upgrade to the latest 3.x release and fix all deprecation notices first.
   Everything that was deprecated in 3.x is removed in 4.0.
2. Switch to PHP 8.5.
3. Work through the sections below.

## PHP 8.5 is required

`composer.json` now requires `"php": "^8.5"`. Composer will not install 4.0 on older PHP versions.

## Removed deprecated API

### `DunningLevelsApi`

Billomat removed `/dunning-levels` (it answers with 404). Reminder levels are reminder texts:

```php
// 3.x
$levels = $billomat->dunningLevels->list();

// 4.0
$levels = $billomat->reminderTexts->list();
```

The `DunningLevel` model is gone as well; use `ReminderText`.

### `AbstractApi::putEmptyResponse()`

Only relevant if you extend `AbstractApi`. Use `putVoid()`:

```php
// 3.x
$this->putEmptyResponse('/invoices/1/complete', $body);

// 4.0
$this->putVoid('/invoices/1/complete', $body);
```

### Read-model properties Billomat no longer returns

These properties were always `null` with the current Billomat API and are removed from the models,
their constructors and `toArray()`. Delete every read of them:

| Model | Removed properties |
|---|---|
| `Article` | `$unit`, `$purchasePriceCurrencyCode`, `$categoryId` |
| `Client` | `$customerportalUrl` |
| `Contact` | `$note` |
| `*Property` | `$position` |
| `TaxRate` | `$accountId` |
| `ReminderText` | `$dueDays` |
| `Country` | `$nameDe`, `$eu` |
| `User` | `$phone`, `$mobile`, `$fax` |
| `Invoice`, `CreditNote` | `$customerportalUrl` |
| `Offer`, `Confirmation` | `$discountRate`, `$discountDate`, `$discountDays`, `$discountAmount`, `$customerportalUrl` |
| `Letter` | `$letterNumber`, `$number`, `$numberPre`, `$numberLength`, `$note`, `$customerportalUrl` |
| `Reminder` | `$reminderNumber`, `$number`, `$numberPre`, `$numberLength`, `$totalNet`, `$quote` |
| `Recurring` | `$numberPre` |
| `Incoming` | `$supplyDate`, `$dueDays`, `$paidAt`, `$intro` |
| `DeliveryNote` | `$totalGross`, `$totalNet`, `$netGross`, `$reduction`, `$totalGrossUnreduced`, `$totalNetUnreduced`, `$currencyCode`, `$quote`, `$customerportalUrl` |
| `InvoiceItem`, `OfferItem`, `CreditNoteItem`, `ConfirmationItem` | `$created` |
| `ReminderItem` | `$totalGross`, `$totalNet`, `$created` |
| `DeliveryNoteItem` | `$unitPrice`, `$taxName`, `$taxRate`, `$reduction`, `$totalGross`, `$totalNet`, `$totalGrossUnreduced`, `$totalNetUnreduced`, `$created` |

Fields that Billomat still accepts on write (for example `Recurring::$numberPre`) stay available
in the matching `*CreateOptions`/`*UpdateOptions`.

If you construct models yourself (for example in tests), switch to named arguments;
positional constructor calls break wherever a removed parameter was in between.

## `BillomatConfig` validates its input

The constructor (and therefore `BillomatClient::create()`) throws an `InvalidArgumentException` when

- the Billomat ID is not a valid subdomain (letters, digits and inner hyphens, at most 63 characters), or
- the base URI does not result in an absolute URI with scheme and host.

```php
// 3.x: built the host mycompany.billomat.net.billomat.net and failed only on the first request
BillomatClient::create(billomatId: 'mycompany.billomat.net', apiKey: $key);

// 4.0: throws InvalidArgumentException right away – pass only the subdomain
BillomatClient::create(billomatId: 'mycompany', apiKey: $key);
```

## PSR-18 instead of Symfony HttpClient

`symfony/http-client` is no longer a dependency. The SDK talks to any PSR-18 client and needs
PSR-17 factories. Install one, for example:

```bash
composer require justpilot/billomat-php-api symfony/http-client nyholm/psr7
# or
composer require justpilot/billomat-php-api guzzlehttp/guzzle
```

If you allow the `php-http/discovery` Composer plugin, it installs a client for you when none is present.

### Passing your own client

`BillomatClient::__construct()` and `BillomatClient::create()` take a `Psr\Http\Client\ClientInterface`.
Wrap a Symfony client in `Psr18Client`:

```php
// 3.x
$billomat = new BillomatClient($config, HttpClient::create(['proxy' => 'http://proxy:3128']));

// 4.0
use Symfony\Component\HttpClient\Psr18Client;

$billomat = new BillomatClient($config, new Psr18Client(HttpClient::create(['proxy' => 'http://proxy:3128'])));
```

`BillomatConfig::$timeout` only applies to the client the SDK creates itself. A client you pass in
uses its own timeout settings.

### Tests with `MockHttpClient`

```php
// 3.x
$http = new BillomatHttpClient(new MockHttpClient($responses), $config);

// 4.0
$http = new BillomatHttpClient(new Psr18Client(new MockHttpClient($responses)), $config);
```

The mock callback receives the JSON payload in `$options['body']` (a string); `$options['json']` is no longer set.
Array query parameters arrive as `key%5B%5D=value`.

### Custom `AbstractApi` subclasses and `BillomatHttpClientInterface`

`BillomatHttpClientInterface::request()` returns a PSR-7 `ResponseInterface` and already throws the SDK
exceptions for 4xx/5xx. `AbstractApi::mapHttpException()` and `HttpException::fromResponse()` are gone.

```php
// 3.x
$response = $this->http->request('GET', "/invoices/{$id}/pdf", $query);
try {
    return $response->getContent();
} catch (HttpExceptionInterface $e) {
    throw $this->mapHttpException($e);
}

// 4.0
return $this->getRaw("/invoices/{$id}/pdf", $query);
// or: (string) $this->http->request('GET', $path, $query)->getBody();
```

### Retries are on by default

4.0 retries 429 responses (after the wait Billomat announces, up to 10 seconds) and GET requests that fail
with 502/503/504 or a network error (up to 2 retries). If you had your own retry layer, remove it or turn the
SDK's off:

```php
use Justpilot\Billomat\Config\RetryPolicy;

$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: $key, retry: RetryPolicy::none());

// Batch jobs that may wait for the next 15-minute window:
$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: $key, retry: new RetryPolicy(maxDelayMs: 900_000));
```

### Exceptions

- A 429 throws `RateLimitException` (a subclass of `HttpException`); `getRetryAfter()` returns the seconds until the next window.
- Network failures throw `TransportException` (a subclass of `BillomatException`) instead of Symfony's `TransportExceptionInterface`.
  If you caught Symfony exceptions, catch `BillomatException` instead.
- Exception messages changed to `Billomat responded with HTTP <status> to <METHOD> /<path>. <Billomat error text>`.
  `getPrevious()` is no longer a Symfony exception. Status code and response body are unchanged.
- A 2xx response whose body is not a JSON object throws `UnexpectedResponseException` instead of `JsonException`.

## Read-only methods are `#[\NoDiscard]`

`get()`, `getMyself()`, `list()`, `listPage()`, `listGrouped()`, `iterateAll()`, `cloud()`,
`pdf()`, `thumb()`, `avatar()` and `SearchApi::query()` carry `#[\NoDiscard]`. PHP 8.5 emits a warning
when their result is ignored. This is not a hard break, but test suites that turn warnings into failures
will notice it. If you call such a method only for its exception (for example to check that a resource
exists), cast the result to `(void)`:

```php
(void) $billomat->clients->get($id);
```

## Sub-resources hang off their parent

Items, payments, comments, tags, properties, property values and recurring e-mail receivers are no
longer properties of `BillomatClient`. They are reached through their parent resource and created on
first access:

```php
// 3.x
$billomat->invoiceComments->listByInvoice($invoiceId);
$billomat->clientTags->create($options);

// 4.0
$billomat->invoices->comments->list($invoiceId);
$billomat->clients->tags->create($options);
```

| 3.x | 4.0 |
|---|---|
| `$billomat->invoiceItems` | `$billomat->invoices->items` |
| `$billomat->invoicePayments` | `$billomat->invoices->payments` |
| `$billomat->invoiceComments` | `$billomat->invoices->comments` |
| `$billomat->invoiceTags` | `$billomat->invoices->tags` |
| `$billomat->offerItems` | `$billomat->offers->items` |
| `$billomat->offerComments` | `$billomat->offers->comments` |
| `$billomat->offerTags` | `$billomat->offers->tags` |
| `$billomat->confirmationItems` | `$billomat->confirmations->items` |
| `$billomat->confirmationComments` | `$billomat->confirmations->comments` |
| `$billomat->confirmationTags` | `$billomat->confirmations->tags` |
| `$billomat->creditNoteItems` | `$billomat->creditNotes->items` |
| `$billomat->creditNotePayments` | `$billomat->creditNotes->payments` |
| `$billomat->creditNoteComments` | `$billomat->creditNotes->comments` |
| `$billomat->creditNoteTags` | `$billomat->creditNotes->tags` |
| `$billomat->deliveryNoteItems` | `$billomat->deliveryNotes->items` |
| `$billomat->deliveryNoteComments` | `$billomat->deliveryNotes->comments` |
| `$billomat->deliveryNoteTags` | `$billomat->deliveryNotes->tags` |
| `$billomat->recurringItems` | `$billomat->recurrings->items` |
| `$billomat->recurringTags` | `$billomat->recurrings->tags` |
| `$billomat->recurringEmailReceivers` | `$billomat->recurrings->emailReceivers` |
| `$billomat->reminderItems` | `$billomat->reminders->items` |
| `$billomat->reminderTags` | `$billomat->reminders->tags` |
| `$billomat->letterComments` | `$billomat->letters->comments` |
| `$billomat->letterTags` | `$billomat->letters->tags` |
| `$billomat->incomingComments` | `$billomat->incomings->comments` |
| `$billomat->incomingPayments` | `$billomat->incomings->payments` |
| `$billomat->incomingTags` | `$billomat->incomings->tags` |
| `$billomat->incomingProperties` | `$billomat->incomings->properties` |
| `$billomat->incomingPropertyValues` | `$billomat->incomings->propertyValues` |
| `$billomat->articleTags` | `$billomat->articles->tags` |
| `$billomat->articleProperties` | `$billomat->articles->properties` |
| `$billomat->articlePropertyValues` | `$billomat->articles->propertyValues` |
| `$billomat->clientTags` | `$billomat->clients->tags` |
| `$billomat->clientProperties` | `$billomat->clients->properties` |
| `$billomat->clientPropertyValues` | `$billomat->clients->propertyValues` |
| `$billomat->supplierTags` | `$billomat->suppliers->tags` |
| `$billomat->supplierProperties` | `$billomat->suppliers->properties` |
| `$billomat->supplierPropertyValues` | `$billomat->suppliers->propertyValues` |
| `$billomat->userProperties` | `$billomat->users->properties` |

### One generic class per kind of sub-resource

The 39 classes like `InvoiceCommentsApi` or `ClientTagsApi` are replaced by `CommentsApi`, `TagsApi`,
`ItemsApi`, `PaymentsApi`, `PropertiesApi` and `PropertyValuesApi`. Type hints on the old classes have to
change:

```php
// 3.x
public function __construct(private InvoiceCommentsApi $comments) {}

// 4.0
/** @param CommentsApi<InvoiceComment, InvoiceCommentCreateOptions, InvoiceCommentActionKey> $comments */
public function __construct(private CommentsApi $comments) {}
```

The `@template` types tell PHPStan and IDEs which model `get()`/`list()` return. At runtime, `create()`
and `update()` throw an `InvalidArgumentException` if they get the options of another resource, and
`CommentsApi::list()` does the same for another resource's `*CommentActionKey`. Code that constructed
the old classes directly (`new InvoiceCommentsApi($http)`) uses the parent instead:
`new InvoicesApi($http)->comments`. `ReminderItemsApi` and `RecurringEmailReceiversApi` keep their names.

### `listBy{Parent}()` is `list()`

```php
// 3.x
$billomat->invoiceItems->listByInvoice($invoiceId, ['per_page' => 50]);
$billomat->contacts->listByClient($clientId);

// 4.0
$billomat->invoices->items->list($invoiceId, ['per_page' => 50]);
$billomat->contacts->list($clientId);
```

Payments and property values can be listed without a parent, so the parent ID is the optional first
argument there:

```php
// 3.x
$billomat->invoicePayments->list(['invoice_id' => $invoiceId, 'order_by' => 'date+DESC']);

// 4.0
$billomat->invoices->payments->list($invoiceId, ['order_by' => 'date+DESC']);
$billomat->invoices->payments->list(filters: ['from' => '2026-01-01']);
```

## Methods that always returned `true` return `void`

`delete()`, `complete()`, `cancel()`, `uncancel()`, `clear()`, `undo()`, `win()`, `lose()`, `email()`,
`mail()`, `encash()`, `upload()` and `uploadSignature()` never returned `false`; errors were always
exceptions. They now return `void`:

```php
// 3.x
if ($billomat->invoices->complete($id)) {
    // …
}

// 4.0
$billomat->invoices->complete($id); // throws on failure
```
