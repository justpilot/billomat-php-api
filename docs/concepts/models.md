# Read models

Every Billomat response is hydrated into a `final readonly` read model from `Justpilot\Billomat\Model` (`Invoice`, `Client`, `InvoiceItem`, …). The properties are typed: IDs as `int`, amounts as [`Money`](money.md), dates as `DateTimeImmutable`, fixed value sets as enums from `Model\Enum`.

```php
$invoice = $billomat->invoices->getOrFail(42);

$invoice->status;      // ?InvoiceStatus
$invoice->totalGross;  // ?Money
$invoice->taxes;       // list<TaxSummary>
$invoice->items;       // list<InvoiceItem>
$invoice->toArray();   // Billomat field names, amounts as strings
```

Unknown enum values become `null` instead of throwing an exception.

## Required fields: `id`

Every model for an addressable resource has a non-nullable `public int $id` (`IncomingCategory`: `string`), so a create → sub-resource flow needs no null check:

```php
$invoice = $billomat->invoices->create($options);
$billomat->invoices->items->create($invoice->id, $item);   // $invoice->id is int
```

Billomat always sends the ID. If a response doesn't contain a usable one (missing or empty), `fromArray()` throws an `UnexpectedResponseException` naming the model and field (`Unexpected response from Billomat: Invoice without a valid "id" field.`) instead of falling back to `0`. The same applies to `unitPrice` on document items and `amount` on payments. In webhooks, `WebhookPayload::model()` turns this into an `InvalidWebhookException`.

No read model fills a missing value with `0`, `''` or `false`. Fields Billomat always sends are required in the same way (checked against recorded sandbox responses): `clientId` on documents, the parent ID of comments, tags, payments, property values and e-mail receivers, `quantity` on items, `name` and `count` of tags and tag cloud entries, `name` of properties, units, suppliers and tax rates, `rate` and `isDefault` of tax rates, `isDefault` of templates, `code` of countries and currencies, `name`/`rate`/`amount` of a `TaxSummary`, `filename`/`fileSize` of inbox documents, `resource` of activities and search results and the fields of `AccountQuota`. Every other field is nullable; `Client::$name` is `null` for private clients without a company name.

The ID stays `?int` where it is not the key of a resource: tag cloud entries (`*TagCloudEntry`), `Country` and `Currency` (keyed by their code), and the references in `Activity` and `SearchResult`. Other foreign keys such as `InvoiceItem::$invoiceId` stay nullable, too.

## Fields the SDK doesn't know: `extra`

Billomat occasionally adds new fields to its API. Anything a generated model doesn't map (yet) ends up unchanged in `$extra` instead of being lost. Handwritten models such as `Account`, `Settings` or `Activity` have no `$extra` (`DocumentPdf` does).

```php
$client = $billomat->clients->getOrFail(7);

$client->extra;             // e.g. ['new_field' => 'value']
$client->toArray();         // contains 'new_field' as well
```

`toArray()` outputs the entries from `extra` first; mapped fields with the same name override them. The values are raw, as Billomat delivers them (mostly strings). If you use a field from `extra` regularly, that's a good reason for an issue or pull request so the model maps it with a proper type.
