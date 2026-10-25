# Read-Modelle

Jede Antwort von Billomat wird in ein `final readonly` Read-Modell aus `Justpilot\Billomat\Model` hydriert (`Invoice`, `Client`, `InvoiceItem`, …). Die Properties sind typisiert: IDs als `int`, Beträge als [`Money`](money.md), Datumswerte als `DateTimeImmutable`, feste Wertemengen als Enums aus `Model\Enum`.

```php
$invoice = $billomat->invoices->getOrFail(42);

$invoice->status;      // ?InvoiceStatus
$invoice->totalGross;  // ?Money
$invoice->items;       // list<InvoiceItem>
$invoice->toArray();   // Billomat-Feldnamen, Beträge als String
```

Unbekannte Enum-Werte werden zu `null`, statt eine Exception zu werfen.

## Felder, die das SDK nicht kennt: `extra`

Billomat ergänzt seine API gelegentlich um neue Felder. Was ein generiertes Modell (noch) nicht abbildet, landet unverändert in `$extra`, statt verloren zu gehen:

```php
$client = $billomat->clients->getOrFail(7);

$client->extra;             // z. B. ['new_field' => 'Wert']
$client->toArray();         // enthält 'new_field' ebenfalls
```

`toArray()` gibt die Einträge aus `extra` zuerst aus; gleichnamige abgebildete Felder überschreiben sie. Die Werte sind roh, wie Billomat sie liefert (meist Strings). Nutzt du ein Feld aus `extra` regelmäßig, ist das ein guter Anlass für ein Issue oder einen Pull Request, damit das Modell es typisiert abbildet.
