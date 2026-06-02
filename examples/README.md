# Beispiele

Dieser Ordner enthält lauffähige PHP-Skripte, die typische Workflows mit dem SDK zeigen. Sie sind absichtlich kurz und kommentiert.

## Voraussetzungen

- PHP 8.4+
- `composer install` im Projekt-Root ausgeführt
- Gültige Billomat-Credentials (idealerweise Sandbox), zugewiesen über Umgebungsvariablen:

  ```bash
  export BILLOMAT_ID=mycompany
  export BILLOMAT_API_KEY=…
  ```

  Optional, falls eine Drittanbieter-App registriert ist:

  ```bash
  export BILLOMAT_APP_ID=…
  export BILLOMAT_APP_SECRET=…
  ```

Tipp: Die Beispiele fischen die Variablen über `getenv()` ab. Du kannst sie auch über eine lokale `.env.test.local` setzen — die wird vom Test-Bootstrap geladen, nicht aber automatisch beim Direktstart der Beispiele. Für die Beispiele also: Variablen vor dem Aufruf in die Shell exportieren oder explizit `BILLOMAT_ID=… php examples/01-create-client.php` vorschalten.

## Beispiele ausführen

```bash
php examples/01-create-client.php
php examples/02-create-invoice.php
php examples/03-complete-and-pdf.php
php examples/04-payments.php
php examples/05-list-with-filters.php
php examples/06-error-handling.php
php examples/07-create-offer.php
php examples/08-credit-note.php
php examples/09-recurring.php
php examples/10-incoming.php
php examples/11-supplier.php
php examples/12-email-invoice.php
```

## Übersicht

| Datei | Was es zeigt |
|---|---|
| [`01-create-client.php`](01-create-client.php) | Einen Kunden anlegen und die ID ausgeben. |
| [`02-create-invoice.php`](02-create-invoice.php) | Rechnung mit zwei Positionen in einem Call anlegen. |
| [`03-complete-and-pdf.php`](03-complete-and-pdf.php) | Rechnung abschließen und das PDF lokal speichern. |
| [`04-payments.php`](04-payments.php) | Zahlung verbuchen, listen, wieder entfernen. |
| [`05-list-with-filters.php`](05-list-with-filters.php) | Filter, Pagination, Sortierung mit `order_by=…+DESC`. |
| [`06-error-handling.php`](06-error-handling.php) | Demonstriert `ValidationException` und `NotFoundException`. |
| [`07-create-offer.php`](07-create-offer.php) | Angebot anlegen, abschliessen, als gewonnen markieren. |
| [`08-credit-note.php`](08-credit-note.php) | Gutschrift anlegen, abschliessen, Auszahlung verbuchen. |
| [`09-recurring.php`](09-recurring.php) | Abo-Rechnung mit E-Mail-Empfänger, Tag und Preis-Update. |
| [`10-incoming.php`](10-incoming.php) | Eingangsrechnung erfassen, taggen, Zahlung verbuchen. |
| [`11-supplier.php`](11-supplier.php) | Lieferant mit Tag und Property-Wert anlegen. |
| [`12-email-invoice.php`](12-email-invoice.php) | Rechnung per `InvoiceEmailOptions` an Kunden versenden. |

## Daten aufräumen

Mehrere Beispiele legen reale Datensätze im verbundenen Account an. Im Billomat-Sandbox ist das harmlos. In einem Produktiv-Account besser **nicht** ausführen — oder die angelegten IDs am Ende manuell löschen.
