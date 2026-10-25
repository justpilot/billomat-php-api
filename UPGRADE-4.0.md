# Upgrading from 3.x to 4.0

This guide lists every backward-incompatible change in 4.0 with a before/after example.
It grows with each 4.0 pre-release; the [CHANGELOG](CHANGELOG.md) has the full list of changes.

Recommended order:

1. Upgrade to the latest 3.x release and fix all deprecation notices first.
   Everything that was deprecated in 3.x is removed in 4.0.
2. Switch to PHP 8.5.
3. Run the Rector set (next section) for the mechanical changes.
4. Work through the sections below for the rest.

## Automated upgrade with Rector

The package ships a [Rector](https://getrector.com) set, `rector/upgrade-4.0.php`, that rewrites the
mechanical parts of the upgrade. Install Rector if your project doesn't have it, update the SDK, then
run the set **before** you change anything by hand:

```bash
composer require --dev rector/rector
composer require justpilot/billomat-php-api:^4.0

vendor/bin/rector process src tests --config vendor/justpilot/billomat-php-api/rector/upgrade-4.0.php --dry-run
vendor/bin/rector process src tests --config vendor/justpilot/billomat-php-api/rector/upgrade-4.0.php
```

Run with `--config` like this, Rector writes new class names fully qualified and leaves the old `use`
statements behind. To get imports instead, put the set into a `rector.php` of your own:

```php
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSets([__DIR__ . '/vendor/justpilot/billomat-php-api/rector/upgrade-4.0.php'])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true);
```

Run your code style fixer afterwards; Rector does not sort imports.

### What the set changes

| 3.x | 4.0 |
|---|---|
| `$billomat->invoiceComments`, `$billomat->clientTags`, … (all 39 flat sub-resources) | `$billomat->invoices->comments`, `$billomat->clients->tags`, … |
| `$billomat->dunningLevels`, `DunningLevelsApi`, `DunningLevel` | `$billomat->reminderTexts`, `ReminderTextsApi`, `ReminderText` |
| `listByInvoice($id)`, `listByOffer($id)`, … on sub-resources, `ContactsApi::listByClient()` | `list($id)` |
| Named arguments `invoiceId:`, `offerId:`, … on comments, tags and items | `parentId:` |
| `list($filters)`, `listPage($filters)`, `iterateAll($filters, $size)` on payments and property values | `list(filters: $filters)`, … |
| `new InvoiceCommentsApi($http)` and the other removed sub-resource classes | `new InvoicesApi($http)->comments` |
| Type hints on `InvoiceCommentsApi`, `ClientTagsApi`, … | `CommentsApi`, `TagsApi`, … |
| `InvoiceEmailOptions`, `OfferEmailOptions`, … | `EmailOptions` |
| `InvoicePaymentType`, `InvoicePdfType`, `InvoiceItemType`, `ArticleType` | `PaymentType`, `PdfType`, `ItemType` |
| `OfferStatus::ACCEPTED`, `OfferStatus::REJECTED` | `OfferStatus::WON`, `OfferStatus::LOST` |
| `$options->getItems()` on document create options | `$options->items` |
| `$this->putEmptyResponse()` in `AbstractApi` subclasses | `$this->putVoid()` |
| `pdf($id, $type, rawPdf: true)` / `pdf($id, $type, true)` on the document APIs | `pdfContent($id, $type)` (`rawPdf: false` is dropped) |
| `create(new InvoicePaymentCreateOptions(invoiceId: $id, …))` and the other payment, comment, tag, property value and e-mail receiver options | `create($id, new InvoicePaymentCreateOptions(…))` |

The set only touches calls it can attribute to the SDK: `$billomat->invoiceComments` is rewritten when
Rector (via PHPStan) knows that `$billomat` is a `BillomatClient`. Untyped variables, values from a
service container without a type and `mixed` stay as they are. Check the diff and search for the
remaining 3.x names afterwards.

### What stays manual

These changes need a decision and are not automated. The sections below describe each one.

- Amounts are `Money` instead of `float`, tax rows are `TaxSummary` objects.
- Options are immutable: property assignments and `addItem()` become named arguments or `with*()`.
- Enums instead of strings (`taxRule: 'NO_TAX'` → `TaxRule::NO_TAX`), payment types as `list<PaymentType>`,
  `InvoiceMailOptions::$paperWeight` as `int`.
- Methods that returned `true` return `void`: rewrite `if ($api->complete($id))` and similar.
- Removed read-model properties, including the fields of `DunningLevel` that `ReminderText` doesn't have
  (`$position`, `$dueDays`, `$charge`, `$interest`).
- `@template` types for the generic sub-resource classes (`CommentsApi<InvoiceComment, …>`) in your PHPDoc.
- `pdf(rawPdf: $flag)` with a flag that is only known at runtime: split it into `pdf()` and `pdfContent()`.
- Payment, comment, tag, property value and e-mail receiver options built outside the `create()` call
  (`$options = new InvoiceTagCreateOptions(invoiceId: $id, …)`): move the ID into `create($id, $options)`.
- The parent filter of payments and property values: `list(filters: ['invoice_id' => $id])` keeps
  working, `list($id)` is the 4.0 way.
- PSR-18 setup, `MockHttpClient` in tests, exception handling, `#[\NoDiscard]`, `BillomatConfig` validation.

## PHP 8.5 is required

`composer.json` now requires `"php": "^8.5"` and `ext-bcmath` (for `Money`, see below). Composer will not install 4.0 on older PHP versions or without bcmath.

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
  A `RateLimitException` that reaches your code means the SDK's retries are exhausted or the wait exceeds `RetryPolicy::$maxDelayMs`.
- `getRetryAfter()` lives on `HttpException` and is also set for 502/503/504 when Billomat sends `Retry-After`.
  If you read `Retry-After` from the Symfony exception in `getPrevious()`, use `getRetryAfter()` instead – `getPrevious()` no longer carries the response.
- Use `isTransient()` and `mayHaveBeenProcessed()` (on every `BillomatException`) instead of checking status codes or exception classes:

  ```php
  // Before (3.x)
  } catch (HttpException $e) {
      $previous = $e->getPrevious();
      $retryAfter = $previous instanceof HttpExceptionInterface
          ? $previous->getResponse()->getHeaders(false)['retry-after'][0] ?? null
          : null;
      $unavailable = \in_array($e->getStatusCode(), [429, 502, 503, 504], true);
  }

  // After (4.0)
  } catch (BillomatException $e) {
      if ($e->isTransient()) {
          $job->releaseIn($e instanceof HttpException ? $e->getRetryAfter() ?? 60 : 60);
      }
      if ($e->mayHaveBeenProcessed()) {
          // A 5xx or a timeout on POST: check with get()/list() before sending it again.
      }
  }
  ```
- Network failures throw `TransportException` (a subclass of `BillomatException`) instead of Symfony's `TransportExceptionInterface`.
  If you caught Symfony exceptions, catch `BillomatException` instead.
- Exception messages changed to `Billomat responded with HTTP <status> to <METHOD> /<path>. <Billomat error text>`.
  `getPrevious()` is no longer a Symfony exception. Status code and response body are unchanged.
- A 2xx response whose body is not a JSON object throws `UnexpectedResponseException` instead of `JsonException`.

### `pdf(rawPdf: true)` is `pdfContent()`

The document APIs (invoices, offers, confirmations, credit notes, delivery notes, letters and reminders)
split `pdf()` into two methods: `pdf()` always returns the `*Pdf` model (metadata plus base64 content),
`pdfContent()` the binary PDF. The `bool $rawPdf` flag is gone, and so is the `InvoicePdf|string` return
type you had to narrow:

```php
// 3.x
$pdf = $billomat->invoices->pdf($id, PdfType::SIGNED, rawPdf: true); // InvoicePdf|string
$model = $billomat->invoices->pdf($id);                              // InvoicePdf|string

// 4.0
$pdf = $billomat->invoices->pdfContent($id, PdfType::SIGNED); // string
$model = $billomat->invoices->pdf($id);                       // InvoicePdf
```

`pdfContent()` also throws `UnexpectedResponseException` when Billomat answers 2xx with an empty body or a
body that is not a PDF (no `%PDF-` header). 3.x returned such bodies as they were. If you checked the result
yourself, you can drop that check:

```php
// 3.x
$pdf = $billomat->invoices->pdf($id, rawPdf: true);
if ('' === $pdf || !str_starts_with($pdf, '%PDF-')) {
    throw new RuntimeException('Billomat did not return a PDF');
}

// 4.0
$pdf = $billomat->invoices->pdfContent($id); // throws UnexpectedResponseException
```

## Read-only methods are `#[\NoDiscard]`

`get()`, `getMyself()`, `list()`, `listPage()`, `listGrouped()`, `iterateAll()`, `cloud()`,
`pdf()`, `pdfContent()`, `thumb()`, `avatar()` and `SearchApi::query()` carry `#[\NoDiscard]`. PHP 8.5 emits a warning
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
$billomat->clients->tags->create($clientId, $options); // see "The parent ID is an argument of create()" below
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
$billomat->invoices->payments->list($invoiceId, new InvoicePaymentFilter(orderBy: OrderBy::desc('date')));
$billomat->invoices->payments->list(filters: new InvoicePaymentFilter(from: new DateTimeImmutable('2026-01-01')));
```

Plain arrays keep working. Payments and property values have typed filters (`InvoicePaymentFilter`,
`ClientPropertyValueFilter`, …); the other sub-resources take a `SubResourceFilter` for sorting and
pagination, and all of them have `listPage()` and `iterateAll()`:

```php
$billomat->invoices->items->list($invoiceId, new SubResourceFilter(orderBy: OrderBy::asc('position')));
$billomat->invoices->comments->list($invoiceId, filters: new SubResourceFilter(perPage: 20));
```

### The parent ID is an argument of `create()`

Payments, comments, tags, property values and recurring e-mail receivers take the parent ID as the first
argument of `create()`, like items already did and like `list($parentId)`. The parent ID field is gone from
their create options (`invoiceId`, `offerId`, `clientId`, `recurringId`, …, and their `with…Id()` methods):

```php
// 3.x
$billomat->invoicePayments->create(new InvoicePaymentCreateOptions(invoiceId: $invoiceId, amount: 119.0));
$billomat->clientTags->create(new ClientTagCreateOptions(clientId: $clientId, name: 'VIP'));
$billomat->recurringEmailReceivers->create(new RecurringEmailReceiverCreateOptions(recurringId: $id, type: RecurringEmailReceiverType::CC, address: 'a@example.com'));

// 4.0
$billomat->invoices->payments->create($invoiceId, new InvoicePaymentCreateOptions(amount: Money::of('119')));
$billomat->clients->tags->create($clientId, new ClientTagCreateOptions(name: 'VIP'));
$billomat->recurrings->emailReceivers->create($id, new RecurringEmailReceiverCreateOptions(type: RecurringEmailReceiverType::CC, address: 'a@example.com'));
```

Positional calls change too: `new InvoiceTagCreateOptions($invoiceId, 'VIP')` becomes
`create($invoiceId, new InvoiceTagCreateOptions('VIP'))`.

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

## Status methods without a Billomat endpoint are removed

Billomat has no `PUT …/undo` endpoint (404), and delivery notes and letters can't be canceled or
cleared through the API (`cancel` answers 400 "cancellation not supported", `clear` 404). The methods
that called these endpoints are removed:

| Removed | Replacement |
|---|---|
| `OffersApi::undo()` | `uncancel()` (`CANCELED → OPEN`) or `unclear()` (`CLEARED → WON`) |
| `ConfirmationsApi::undo()` | `uncancel()` (`CANCELED → COMPLETED`) or `unclear()` (`CLEARED → COMPLETED`) |
| `DeliveryNotesApi::cancel()`, `clear()`, `undo()` | none (Billomat offers no endpoint) |
| `LettersApi::cancel()`, `clear()`, `undo()` | none (Billomat offers no endpoint) |

```php
// 3.x
$billomat->offers->cancel($id);
$billomat->offers->undo($id);          // HTTP 404

$billomat->confirmations->clear($id);
$billomat->confirmations->undo($id);   // HTTP 404

// 4.0
$billomat->offers->cancel($id);
$billomat->offers->uncancel($id);

$billomat->confirmations->clear($id);
$billomat->confirmations->unclear($id);
```

`RemindersApi` gains `uncancel()` as well.

## Options are immutable

All `*CreateOptions`/`*UpdateOptions`, `EmailOptions`, `InvoiceMailOptions`,
`InboxDocumentCreateOptions` and `SettingsUpdateOptions` are `final readonly` classes. Every field
is a constructor parameter with a default, so pass named arguments instead of assigning properties.
Required fields (e.g. `clientId` on `InvoiceCreateOptions`) are still the first positional
parameters:

```php
// 3.x
$options = new ClientCreateOptions();
$options->name = 'ACME GmbH';
$options->email = 'info@acme.test';

// 4.0
$options = new ClientCreateOptions(name: 'ACME GmbH', email: 'info@acme.test');
```

To change a field later, or to build options step by step, use the `with*()` methods. They return
a copy and leave the original untouched:

```php
// 3.x
if (null !== $from) {
    $options->from = $from;
}

// 4.0
if (null !== $from) {
    $options = $options->withFrom($from);
}
```

Spreading an array of named arguments works too: `new ClientCreateOptions(...$fields)`.

### Positions go into the constructor

`addItem()` and `getItems()` are gone from the document create options (invoices, offers,
confirmations, credit notes, delivery notes, recurrings):

```php
// 3.x
$options = new InvoiceCreateOptions($clientId);
$options->addItem($item);
$items = $options->getItems();

// 4.0
$options = new InvoiceCreateOptions($clientId, items: [$item]);
$options = $options->withItem($another);
$items = $options->items;
```

### Clearing a field with `Clear::Field`

In 3.x, `null` meant "don't send", so an update could not empty a field. In `*UpdateOptions` and
`SettingsUpdateOptions`, `null` still means "not sent, unchanged"; `Clear::Field` is sent as JSON
`null`, and Billomat empties the field:

```php
use Justpilot\Billomat\Api\Clear;

$billomat->clients->update($id, new ClientUpdateOptions(note: Clear::Field));
```

### Item updates take `*ItemUpdateOptions`

`ItemsApi::update()` on invoices, offers, credit notes, confirmations, delivery notes and
recurrings takes the new `*ItemUpdateOptions` instead of `*ItemCreateOptions`. All fields are
optional, only the ones you set are sent, and Billomat keeps the rest of the item:

```php
// 3.x: quantity and unit price had to be repeated
$billomat->invoices->items->update($item->id, new InvoiceItemCreateOptions(
    quantity: $item->quantity,
    unitPrice: Money::of('24.90'),
    title: $item->title,
));

// 4.0
$billomat->invoices->items->update($item->id, new InvoiceItemUpdateOptions(unitPrice: Money::of('24.90')));
```

Passing `*ItemCreateOptions` to `update()` throws an `InvalidArgumentException`.

### One `EmailOptions` class

`InvoiceEmailOptions`, `OfferEmailOptions`, `ConfirmationEmailOptions`, `CreditNoteEmailOptions`,
`DeliveryNoteEmailOptions`, `ReminderEmailOptions` and `LetterEmailOptions` had identical fields and
are merged into `EmailOptions`:

```php
// 3.x
$billomat->invoices->email($id, $invoiceEmailOptions);

// 4.0
$billomat->invoices->email($id, new EmailOptions(to: ['kunde@example.test'], subject: 'Ihre Rechnung'));
```

### `ClientCreateOptions::$dunningRun` defaults to `null`

3.x always sent `dunning_run=0`. 4.0 sends it only when you set it, so Billomat's account default
applies. Pass `dunningRun: false` to keep the old behaviour.

## Read-model IDs are `int`

`$id` is a non-nullable `int` on every model of an addressable resource (documents, clients,
suppliers, articles, contacts, items, payments, comments, tags, properties, property values,
settings models, `User`, `Role`, `InboxDocument`, …; `IncomingCategory::$id` is a `string`).
Null checks and fallbacks on the ID can go:

```php
// 3.x
$invoice = $billomat->invoices->create($options);
if (null === $invoice->id) {
    throw new RuntimeException('No ID');
}
$billomat->invoices->items->create($invoice->id, $item);

// 4.0
$invoice = $billomat->invoices->create($options);
$billomat->invoices->items->create($invoice->id, $item);
```

If Billomat ever answers without a usable `id`, `fromArray()` throws an
`UnexpectedResponseException` (`Unexpected response from Billomat: Invoice without a valid "id"
field.`) instead of returning a model with `null`. The same goes for `unitPrice` on document items
and `amount` on payments, which used to fall back to `Money::zero()`. `WebhookPayload::model()`
turns this into an `InvalidWebhookException`. Code that hydrates models from its own arrays (tests,
fixtures) has to include these fields.

The ID stays `?int` on `*TagCloudEntry`, `Country`, `Currency`, `Activity` and `SearchResult`.

### Read-model constructors

If you build read models yourself (e.g. in tests), only the required fields are mandatory and come
first; every other parameter has a default. Use named arguments:

```php
// 3.x: every core field was a positional parameter without default
new InvoiceItem(7, 1, null, 1, 'Stück', 2.0, Money::of('10'), …);

// 4.0
new InvoiceItem(id: 7, unitPrice: Money::of('10'), quantity: 2.0);
```

## Amounts are `Money` instead of `float`

All monetary fields are `Justpilot\Billomat\Model\Money` instead of `float`: totals (`totalGross`, `totalNet`,
`totalGrossUnreduced`, `totalNetUnreduced`, `totalReduction`), `openAmount`, `paidAmount`, `discountAmount`,
`unitPrice`, `salesPrice` to `salesPrice5`, `purchasePrice`, the `amount` of payments and tax rows, `chargeAmount`,
`total`, `revenueGross`/`revenueNet`/`costsGross`/`costsNet`, `finapiTransactionAmount` and the `InvoiceGroup` totals.
Quantities, tax rates, discount rates and exchange rates (`quote`) stay `float`.

`Money` is exact (no `0.1 + 0.2` errors), keeps Billomat's precision (unit prices have up to four decimals)
and handles negative amounts. The currency stays on the document (`currencyCode`).

Reading:

```php
// 3.x
if ($invoice->openAmount > 0) {
    echo number_format($invoice->openAmount, 2, ',', '.');
}
$open = array_sum(array_map(fn (Invoice $i) => $i->openAmount ?? 0.0, $invoices));

// 4.0
if ($invoice->openAmount?->isPositive()) {
    echo $invoice->openAmount->format(2, ',', '.');
}
$open = Money::sum(...array_map(fn (Invoice $i) => $i->openAmount, $invoices));
```

Writing:

```php
// 3.x
new InvoiceItemCreateOptions(quantity: 3.0, unitPrice: 49.90);
new InvoicePaymentCreateOptions(invoiceId: $id, amount: 119.0);

// 4.0
new InvoiceItemCreateOptions(quantity: 3.0, unitPrice: Money::of('49.90'));
new InvoicePaymentCreateOptions(amount: Money::of('119'));
```

`Money::of()` takes a decimal string with a point or an int; `"19,99"` or `"1e3"` throw an `InvalidArgumentException`.
Where you need a `float` (e.g. for a chart library), use `toFloat()` – precision is lost there.

Payloads send amounts as JSON strings (`"unit_price":"49.9"`), and the models' `toArray()` exports them
as strings in Billomat's form (`"40"`, `"0.1"`), no longer as floats. `json_encode($money)` gives the string, too.

### Tax rows are `TaxSummary` objects

`taxes` on `Invoice`, `Offer`, `CreditNote`, `Confirmation`, `DeliveryNote` and `Recurring` is a list of
`Justpilot\Billomat\Model\TaxSummary` objects instead of arrays.

```php
// 3.x
foreach ($invoice->taxes as $tax) {
    echo $tax['name'], ' ', $tax['rate'], ' ', $tax['amount'];
}

// 4.0
foreach ($invoice->taxes as $tax) {
    echo $tax->name, ' ', $tax->rate, ' ', $tax->amount->format(2, ',', '.');
}
```

`$tax->toArray()` gives the old array shape, with the amount as a string.

## More fields are enums

### `OfferStatus::ACCEPTED`/`REJECTED` are `WON`/`LOST`

Billomat calls won and lost offers `WON` and `LOST`. In 3.x these offers hydrated to `status = null`,
and filtering by `ACCEPTED`/`REJECTED` found nothing.

```php
// 3.x
if ($offer->status === OfferStatus::ACCEPTED) { … }
$billomat->offers->list(new OfferFilter(status: [OfferStatus::REJECTED]));

// 4.0
if ($offer->status === OfferStatus::WON) { … }
$billomat->offers->list(new OfferFilter(status: [OfferStatus::LOST]));
```

`label()` returns „Gewonnen“ and „Verloren“.

### Enums instead of strings

| Field | 3.x | 4.0 |
|---|---|---|
| `Client::$taxRule`, `ClientCreateOptions`/`ClientUpdateOptions` `taxRule` | `?string` | `?TaxRule` |
| `Client::$netGross`, options `netGross` | `?string` | `?NetGross` |
| `Client::$discountRateType`, `$discountDaysType`, `$dueDaysType`, `$reminderDueDaysType`, `$offerValidityDaysType` and the same options | `?string` | `?ValueType` |
| `ClientPropertyValue`, `SupplierPropertyValue`, `ArticlePropertyValue`, `IncomingPropertyValue` `$type` | `?string` | `?PropertyType` |
| `InvoiceGroup::$status` | `?string` | `?InvoiceStatus` |
| `FreeText::$type` | `?string` | `?FreeTextType` |
| `EmailTemplate::$type` | `?string` | `?EmailTemplateType` |

All enums live in `Justpilot\Billomat\Model\Enum`. Unknown values hydrate to `null`; `->value` gives the
Billomat string.

```php
// 3.x
new ClientCreateOptions(name: 'ACME', taxRule: 'NO_TAX', dueDaysType: 'ABSOLUTE', dueDays: 14);
if ($client->netGross === 'GROSS') { … }

// 4.0
new ClientCreateOptions(name: 'ACME', taxRule: TaxRule::NO_TAX, dueDaysType: ValueType::ABSOLUTE, dueDays: 14);
if ($client->netGross === NetGross::GROSS) { … }
```

### `InvoicePaymentType`, `InvoicePdfType` and `InvoiceItemType` are renamed

The three enums are used by every document resource, not just invoices. Their cases are unchanged.
`ArticleType` had the same cases as `InvoiceItemType` and is merged into `ItemType`.

| 3.x | 4.0 |
|---|---|
| `Model\Enum\InvoicePaymentType` | `Model\Enum\PaymentType` |
| `Model\Enum\InvoicePdfType` | `Model\Enum\PdfType` |
| `Model\Enum\InvoiceItemType` | `Model\Enum\ItemType` |
| `Model\Enum\ArticleType` | `Model\Enum\ItemType` (merged, same cases) |

```php
// 3.x
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
$pdf = $billomat->offers->pdf($id, InvoicePdfType::PRINT);

// 4.0
use Justpilot\Billomat\Model\Enum\PdfType;
$pdf = $billomat->offers->pdf($id, PdfType::PRINT);
```

A search and replace of the four class names is enough.

### Payment types are a list of `PaymentType`

`Invoice::$paymentTypes`, `Recurring::$paymentTypes`, `Client::$defaultPaymentTypes` and the matching
`*CreateOptions`/`*UpdateOptions` are `list<PaymentType>` instead of a comma-separated string.
The SDK still sends and exports the comma-separated string.

```php
// 3.x
new InvoiceCreateOptions(clientId: $id, paymentTypes: 'BANK_TRANSFER,PAYPAL');
$acceptsPaypal = str_contains($invoice->paymentTypes ?? '', 'PAYPAL');

// 4.0
new InvoiceCreateOptions(clientId: $id, paymentTypes: [PaymentType::BANK_TRANSFER, PaymentType::PAYPAL]);
$acceptsPaypal = in_array(PaymentType::PAYPAL, $invoice->paymentTypes, true);
```

An empty list (the default) is not sent. In update options, `Clear::Field` empties the field. Values
the enum doesn't know are dropped when hydrating.

### `fromApi()` on every enum, no silent fallback

All enums in `Model\Enum` have `fromApi(?string): ?self`; `null`, `""` and unknown values give `null`.
`RecurringEmailReceiver::$type` is nullable now, because unknown receiver types no longer fall back to `TO`:

```php
// 3.x
$receiver->type->value; // 'To' even if Billomat sent something unknown

// 4.0
$receiver->type?->value; // null for unknown values
```

### Role permissions are `AccessLevel` enums

`Role::$permissions` is `array<string, AccessLevel>` instead of `array<string, ?string>`. Areas without
access (Billomat sends `""`) and unknown values are no longer in the map instead of being `null`.
`Role::toArray()` puts the permissions back as top-level fields (`'articles' => 'DELETE'`), like Billomat
sends them, instead of nesting them under `permissions`. The areas the SDK knows are in `Role::AREAS`.

```php
// 3.x
$canDelete = ($role->permissions['invoices'] ?? null) === 'DELETE';
$noAccess = null === $role->permissions['banking'];
$role->toArray()['permissions']['articles']; // 'DELETE'

// 4.0
$canDelete = ($role->permissions['invoices'] ?? null) === AccessLevel::DELETE;
$canEdit = ($role->permissions['invoices'] ?? null)?->allows(AccessLevel::UPDATE) ?? false; // UPDATE or DELETE
$noAccess = !isset($role->permissions['banking']);
$role->toArray()['articles']; // 'DELETE'
```

`User::$rights` is new and uses the same type.

### `InvoiceMailOptions::$paperWeight` is an `int`

```php
// 3.x
new InvoiceMailOptions(paperWeight: '90');

// 4.0
new InvoiceMailOptions(paperWeight: 90);
```
