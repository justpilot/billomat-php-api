# Geldbeträge (`Money`)

Alle Geldbeträge im SDK sind Instanzen von `Justpilot\Billomat\Model\Money` — in den Read-Modellen genauso wie in den `*CreateOptions`/`*UpdateOptions`. `Money` ist ein unveränderliches Value Object auf Basis von `BcMath\Number` und rechnet exakt mit Dezimalzahlen.

## Warum kein `float`?

`float` ist eine binäre Gleitkommazahl und kann viele Dezimalbeträge nicht exakt darstellen:

```php
var_dump(0.1 + 0.2 === 0.3); // false
var_dump(0.1 + 0.2);         // float(0.30000000000000004)
```

Bei Rechnungssummen, offenen Posten oder Zahlungsabgleichen summieren sich solche Fehler auf, und Vergleiche wie `$paid === $total` gehen schief. Billomat selbst liefert und akzeptiert Beträge als Dezimal-Strings (`"33.61"`, `"40"`, `"12.3456"`) — `Money` übernimmt diese Strings ohne Umweg über `float`.

## Welche Felder sind `Money`?

Alle Betragsfelder, u. a. `unitPrice`, `totalGross`, `totalNet`, `totalGrossUnreduced`, `totalNetUnreduced`, `totalReduction`, `discountAmount`, `openAmount`, `paidAmount`, `amount` (Zahlungen und `TaxSummary::$amount` der Steuerzeilen in `$invoice->taxes`), `salesPrice` bis `salesPrice5`, `purchasePrice`, `chargeAmount`, `costsGross`/`costsNet`, `revenueGross`/`revenueNet`, `finapiTransactionAmount` sowie `InvoiceGroup::$totalGross`/`$totalNet`.

Weiterhin `float` sind Werte, die keine Geldbeträge sind: `quantity`, `taxRate`, `discountRate`, `rate`, `quote` (Wechselkurs) und `discountDays`. `reduction` bleibt ein String, weil Billomat dort sowohl Beträge als auch Prozentangaben (`"10%"`) erlaubt.

## Beträge erzeugen

```php
use Justpilot\Billomat\Model\Money;

$price = Money::of('19.99');   // aus einem Dezimal-String
$fee   = Money::of(5);         // aus einer Ganzzahl
$zero  = Money::zero();

Money::of('19,99');            // InvalidArgumentException: Komma ist kein Dezimaltrenner
Money::of('1e3');              // InvalidArgumentException: keine Exponentialschreibweise
```

`Money::of()` akzeptiert `string`, `int`, `BcMath\Number` und `Money` — bewusst kein `float`, damit kein Rundungsfehler unbemerkt in einen Betrag rutscht.

## Rechnen

Alle Operationen liefern ein neues `Money`; das Original bleibt unverändert. Die Kommentare zeigen `toString()`. Die Argumente dürfen `Money`, ein Dezimal-String oder eine Ganzzahl sein.

```php
$net   = Money::of('100');
$gross = $net->multiply('1.19');       // "119"
$rest  = $gross->subtract('19.50');    // "99.5"
$sum   = $rest->add(Money::of('0.5')); // "100"

Money::of('19.99')->multiply('0.075')->round(); // "1.5" (1.49925, kaufmännisch auf 2 Stellen)
$net->negate();                        // "-100"
Money::of('-12.30')->abs();            // "12.3"
```

`multiply()` rundet nicht; für Anzeige oder Weiterverarbeitung `round(int $scale = 2, RoundingMode $mode = RoundingMode::HalfAwayFromZero)` aufrufen.

### Summe offener Beträge

`Money::sum()` nimmt beliebig viele Beträge entgegen, `null` zählt dabei als 0 — praktisch für nullable Felder wie `openAmount`:

```php
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\Money;

$open = $billomat->invoices->list(new InvoiceFilter(status: [InvoiceStatus::OPEN]));

$total = Money::sum(...array_map(static fn (Invoice $i): ?Money => $i->openAmount, $open));

echo $total->format(2, ',', '.'); // "1.234,50"
```

## Vergleichen

`==`, `<` und `>` funktionieren auf Objekten nicht sinnvoll. Stattdessen:

```php
if ($invoice->openAmount?->isPositive()) {
    // noch offen
}

$invoice->totalGross?->equals('119');        // true auch bei "119.00"
$payment->amount->isGreaterThan('1000');
$payment->amount->isLessThan($invoice->totalGross ?? Money::zero());
$a->compareTo($b);                            // -1, 0 oder 1

$amount->isZero();
$amount->isNegative();
```

`equals()` vergleicht den Wert, nicht die Schreibweise: `Money::of('40')->equals('40.00')` ist `true`.

## Negative Beträge

Negative Beträge sind vollständig unterstützt, etwa für Gutschriften oder Korrekturpositionen:

```php
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Model\Money;

$item = new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: Money::of('-19.90'), title: 'Rückerstattung');

$creditNote = $billomat->creditNotes->get($id);
if ($creditNote?->totalGross?->isNegative()) {
    echo 'Erstattung: '.$creditNote->totalGross->abs()->format(2, ',', '.');
}
```

## Formatieren

`format(int $decimals = 2, string $decimalSeparator = '.', string $thousandsSeparator = '')` liefert eine feste Anzahl Nachkommastellen, kaufmännisch gerundet:

```php
$amount = Money::of('1234.5');

$amount->format();             // "1234.50"
$amount->format(2, ',', '.');  // "1.234,50" (deutsche Schreibweise)
$amount->format(0);            // "1235"
Money::of('-12.3456')->format(2, ',', '.'); // "-12,35"
```

`toString()` (bzw. `(string) $amount`) gibt dagegen die kürzeste exakte Form ohne überflüssige Nullen zurück, so wie Billomat sie liefert: `"40"`, `"0.1"`, `"-12.3456"`. Für Ausgaben an Menschen ist `format()` die richtige Wahl, für Logs und Vergleiche in Tests `toString()`.

## Die Währung steht am Dokument

`Money` kennt keine Währung. Billomat führt die Währung pro Dokument, nicht pro Betrag — sie steht in `currencyCode` der Rechnung, des Angebots, der Gutschrift usw.:

```php
printf('%s %s', $invoice->totalGross?->format(2, ',', '.') ?? '-', $invoice->currencyCode);
// "1.190,00 EUR"
```

Beträge aus Dokumenten mit unterschiedlicher Währung sollten deshalb nicht ohne Umrechnung (z. B. über `quote`) addiert werden — `Money` verhindert das nicht.

## Beträge schreiben

Write-Optionen erwarten `Money` für alle Betragsfelder:

```php
use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\Api\Clear;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Model\Money;

$item = new InvoiceItemCreateOptions(quantity: 3.0, unitPrice: Money::of('49.90'));

$payment = new InvoicePaymentCreateOptions(invoiceId: 98765, amount: Money::of('100'))
    ->withAmount(Money::of('119'));

// Update-Optionen akzeptieren Money|Clear|null
$update = new ArticleUpdateOptions(salesPrice: Money::of('24.90'), purchasePrice: Clear::Field);
```

Im Payload landet der Betrag als JSON-String in kanonischer Form: `{"unit_price": "49.9"}`. Billomat akzeptiert das genauso wie eine Zahl, aber ohne Umweg über `float`.

## JSON und `toArray()`

`Money` implementiert `JsonSerializable` und wird als String serialisiert. `toArray()` der Read-Modelle exportiert Beträge ebenfalls als Strings:

```php
json_encode(['total' => Money::of('119.00')]); // {"total":"119"}

$invoice->toArray()['total_gross'];            // "119"
```

Wer Beträge in einer eigenen Datenbank speichert, sollte eine `DECIMAL`-Spalte verwenden und `toString()` schreiben.

## Genauigkeit

`Money` hat keine feste Anzahl Nachkommastellen. Billomat speichert Einzelpreise (`unitPrice`) mit bis zu vier Stellen (`"12.3456"`), Summen in der Regel mit zwei. Alle Stellen bleiben beim Lesen, Rechnen und Zurückschreiben erhalten; gerundet wird nur, wenn du `round()` oder `format()` aufrufst.

## `toFloat()` — nur als Notausgang

`toFloat()` wandelt den Betrag in einen `float` um, etwa für eine Chart-Bibliothek oder eine API, die nur Zahlen kennt. Dabei geht die Exaktheit verloren — damit nicht weiterrechnen und nicht vergleichen. Für Berechnungen mit BcMath direkt gibt es `toNumber()`, das das zugrundeliegende `BcMath\Number` liefert.

```php
$chartValue = $invoice->totalGross?->toFloat() ?? 0.0; // nur für die Darstellung
```
