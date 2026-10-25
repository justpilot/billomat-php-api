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

## Fields the SDK doesn't know: `extra`

Billomat occasionally adds new fields to its API. Anything a generated model doesn't map (yet) ends up unchanged in `$extra` instead of being lost. Handwritten models such as `Account`, `Settings`, `Activity` or the `*Pdf` classes have no `$extra`.

```php
$client = $billomat->clients->getOrFail(7);

$client->extra;             // e.g. ['new_field' => 'value']
$client->toArray();         // contains 'new_field' as well
```

`toArray()` outputs the entries from `extra` first; mapped fields with the same name override them. The values are raw, as Billomat delivers them (mostly strings). If you use a field from `extra` regularly, that's a good reason for an issue or pull request so the model maps it with a proper type.
