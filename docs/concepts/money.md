# Amounts (`Money`)

Every monetary amount in the SDK is an instance of `Justpilot\Billomat\Model\Money` — in the read models as well as in the `*CreateOptions`/`*UpdateOptions`. `Money` is an immutable value object based on `BcMath\Number` (requires `ext-bcmath`) and calculates exactly with decimal numbers.

## Why not `float`?

`float` is a binary floating-point number and can't represent many decimal amounts exactly:

```php
var_dump(0.1 + 0.2 === 0.3); // false
var_dump(0.1 + 0.2);         // float(0.30000000000000004)
```

For invoice totals, open items or payment reconciliation these errors add up, and comparisons like `$paid === $total` fail. Billomat itself returns and accepts amounts as decimal strings (`"33.61"`, `"40"`, `"12.3456"`) — `Money` takes these strings without a detour through `float`.

## Which fields are `Money`?

All amount fields, among them `unitPrice`, `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced`, `totalReduction`, `discountAmount`, `openAmount`, `paidAmount`, `amount` (payments and `TaxSummary::$amount` of the tax rows in `$invoice->taxes`), `total` (reminder items), `salesPrice` to `salesPrice5`, `purchasePrice`, `chargeAmount`, `costsGross`/`costsNet`, `revenueGross`/`revenueNet`, `finapiTransactionAmount` and `InvoiceGroup::$totalGross`/`$totalNet`.

Values that aren't amounts of money stay `float`: `quantity`, `taxRate`, `discountRate`, `rate`, `quote` (exchange rate) and `discountDays`. `reduction` stays a string on the read models, because Billomat allows both amounts and percentages (`"10%"`) there.

## Creating amounts

```php
use Justpilot\Billomat\Model\Money;

$price = Money::of('19.99');   // from a decimal string
$fee   = Money::of(5);         // from an integer
$zero  = Money::zero();

Money::of('19,99');            // InvalidArgumentException: comma is not a decimal separator
Money::of('1e3');              // InvalidArgumentException: no exponential notation
```

`Money::of()` accepts `string`, `int`, `BcMath\Number` and `Money` — deliberately no `float`, so that no rounding error slips into an amount unnoticed.

## Calculating

Every operation returns a new `Money`; the original stays unchanged. The comments show `toString()`. Arguments can be `Money`, a decimal string or an integer.

```php
$net   = Money::of('100');
$gross = $net->multiply('1.19');       // "119"
$rest  = $gross->subtract('19.50');    // "99.5"
$sum   = $rest->add(Money::of('0.5')); // "100"

Money::of('19.99')->multiply('0.075')->round(); // "1.5" (1.49925, rounded half away from zero to 2 places)
$net->negate();                        // "-100"
Money::of('-12.30')->abs();            // "12.3"
```

`multiply()` doesn't round; for display or further processing call `round(int $scale = 2, RoundingMode $mode = RoundingMode::HalfAwayFromZero)`.

### Sum of open amounts

`Money::sum()` takes any number of amounts; `null` counts as 0 — handy for nullable fields like `openAmount`:

```php
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\Money;

$open = $billomat->invoices->list(new InvoiceFilter(status: [InvoiceStatus::OPEN]));

$total = Money::sum(...array_map(static fn (Invoice $i): ?Money => $i->openAmount, $open));

echo $total->format(2, ',', '.'); // "1.234,50"
```

## Comparing

`==`, `<` and `>` don't work meaningfully on objects. Use these instead:

```php
if ($invoice->openAmount?->isPositive()) {
    // still open
}

$invoice->totalGross?->equals('119');        // true for "119.00" as well
$payment->amount->isGreaterThan('1000');
$payment->amount->isLessThan($invoice->totalGross ?? Money::zero());
$a->compareTo($b);                            // -1, 0 or 1

$amount->isZero();
$amount->isNegative();
```

`equals()` compares the value, not the notation: `Money::of('40')->equals('40.00')` is `true`.

## Negative amounts

Negative amounts are fully supported, e.g. for credit notes or correction items:

```php
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$item = new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: Money::of('-19.90'), title: 'Refund');

$creditNote = $billomat->creditNotes->get($id);
if ($creditNote?->totalGross?->isNegative()) {
    echo 'Refund: '.$creditNote->totalGross->abs()->format(2, ',', '.');
}
```

## Formatting

`format(int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = '')` returns a fixed number of decimal places, rounded half away from zero:

```php
$amount = Money::of('1234.5');

$amount->format();             // "1234.50"
$amount->format(2, ',', '.');  // "1.234,50" (German notation)
$amount->format(0);            // "1235"
Money::of('-12.3456')->format(2, ',', '.'); // "-12,35"
```

`toString()` (or `(string) $amount`), on the other hand, returns the shortest exact form without trailing zeros, as Billomat delivers it: `"40"`, `"0.1"`, `"-12.3456"`. Use `format()` for output to humans and `toString()` for logs and comparisons in tests.

## The currency belongs to the document

`Money` has no currency. Billomat keeps the currency per document, not per amount — it's in `currencyCode` of the invoice, offer, credit note etc.:

```php
printf('%s %s', $invoice->totalGross?->format(2, ',', '.') ?? '-', $invoice->currencyCode);
// "1.190,00 EUR"
```

Don't add amounts from documents in different currencies without converting them (e.g. via `quote`) — `Money` doesn't prevent it.

## Writing amounts

Write options expect `Money` for all amount fields:

```php
use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\Api\Clear;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Money;

$item = new InvoiceItemCreateOptions(quantity: 3.0, unitPrice: Money::of('49.90'));

$payment = new InvoicePaymentCreateOptions(invoiceId: 98765, amount: Money::of('100'))
    ->withAmount(Money::of('119')); // options are immutable: with*() returns a copy

// Update options accept Money|Clear|null
$update = new ArticleUpdateOptions(salesPrice: Money::of('24.90'), purchasePrice: Clear::Field);
```

In the payload the amount is sent as a JSON string in canonical form: `{"unit_price": "49.9"}`. Billomat accepts this just like a number, but without the detour through `float`.

## JSON and `toArray()`

`Money` implements `JsonSerializable` and serializes as a string. The read models' `toArray()` exports amounts as strings as well:

```php
json_encode(['total' => Money::of('119.00')]); // {"total":"119"}

$invoice->toArray()['total_gross'];            // "119"
```

If you store amounts in your own database, use a `DECIMAL` column and write `toString()`.

## Precision

`Money` has no fixed number of decimal places. Billomat stores unit prices (`unitPrice`) with up to four places (`"12.3456"`), totals usually with two. All places are kept when reading, calculating and writing back; rounding only happens when you call `round()` or `format()`.

## `toFloat()` — escape hatch only

`toFloat()` converts the amount to a `float`, e.g. for a chart library or an API that only understands numbers. Exactness is lost — don't keep calculating or comparing with the result. For calculations with BcMath directly, `toNumber()` returns the underlying `BcMath\Number`.

```php
$chartValue = $invoice->totalGross?->toFloat() ?? 0.0; // display only
```
