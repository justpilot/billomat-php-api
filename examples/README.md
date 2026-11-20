# Examples

This folder contains runnable PHP scripts that show typical workflows with the SDK. They are deliberately short; code comments are in German, like the rest of the code base. CI runs PHPStan (level max), PHP-CS-Fixer and Rector on them, so they stay in sync with the current API.

## Requirements

- PHP 8.5+ with `ext-bcmath`
- `composer install` run in the project root
- Valid Billomat credentials (ideally a sandbox account), passed as environment variables:

  ```bash
  export BILLOMAT_ID=mycompany
  export BILLOMAT_API_KEY=…
  ```

  Optional, if you have registered a third-party app:

  ```bash
  export BILLOMAT_APP_ID=…
  export BILLOMAT_APP_SECRET=…
  ```

All scripts include [`bootstrap.php`](bootstrap.php), which loads the autoloader, reads the environment variables via `getenv()` (a missing required variable stops the script with a hint), builds the `BillomatClient` and prints SDK exceptions as a one-line error instead of a stack trace.

`.env.test.local` is only loaded by the test bootstrap, not by the examples. Export the variables in your shell first, or prefix the call: `BILLOMAT_ID=… BILLOMAT_API_KEY=… php examples/01-create-client.php`.

## Running the examples

```bash
php examples/01-create-client.php
BILLOMAT_CLIENT_ID=12345 php examples/02-create-invoice.php
BILLOMAT_INVOICE_ID=98765 php examples/03-complete-and-pdf.php
BILLOMAT_INVOICE_ID=98765 php examples/04-payments.php
php examples/05-list-with-filters.php
php examples/06-error-handling.php
BILLOMAT_CLIENT_ID=12345 php examples/07-create-offer.php
BILLOMAT_CLIENT_ID=12345 php examples/08-credit-note.php
BILLOMAT_CLIENT_ID=12345 php examples/09-recurring.php
BILLOMAT_SUPPLIER_ID=98765 php examples/10-incoming.php
php examples/11-supplier.php
BILLOMAT_INVOICE_ID=98765 BILLOMAT_EMAIL_TO=you@example.com php examples/12-email-invoice.php
```

## Overview

| File | What it shows |
|---|---|
| [`01-create-client.php`](01-create-client.php) | Create a client and print its ID. |
| [`02-create-invoice.php`](02-create-invoice.php) | Create an invoice with two items in one call (`withItem()`, `Money`). |
| [`03-complete-and-pdf.php`](03-complete-and-pdf.php) | Complete an invoice and save the signed PDF (`PdfType::SIGNED`) locally. |
| [`04-payments.php`](04-payments.php) | Record a payment (`PaymentType`), list payments, delete it again. |
| [`05-list-with-filters.php`](05-list-with-filters.php) | Typed filters (`ClientFilter`, `InvoiceFilter`), pagination and sorting with `OrderBy`. |
| [`06-error-handling.php`](06-error-handling.php) | `get()` vs. `getOrFail()`, `ValidationException` and `AuthenticationException`. |
| [`07-create-offer.php`](07-create-offer.php) | Create an offer, complete it and mark it as won. |
| [`08-credit-note.php`](08-credit-note.php) | Create a credit note, complete it and record the payout. |
| [`09-recurring.php`](09-recurring.php) | Recurring invoice with an email receiver, a tag and a price update. |
| [`10-incoming.php`](10-incoming.php) | Record an incoming invoice, tag it and record a payment. |
| [`11-supplier.php`](11-supplier.php) | Create a supplier with a tag and a property value. |
| [`12-email-invoice.php`](12-email-invoice.php) | Send an invoice to the client with `EmailOptions`. |

## Clean-up

Several examples create real records in the connected account. That's harmless in the Billomat sandbox. In a production account, **don't** run them, or delete the created records by hand afterwards.
