---
layout: home

hero:
  name: Billomat PHP SDK
  text: Typed access to the Billomat API
  tagline: Invoices, offers, clients, payments, PDFs and webhooks for PHP 8.5, on top of any PSR-18 client.
  actions:
    - theme: brand
      text: Get started
      link: /configuration
    - theme: alt
      text: Resources
      link: /resources/
    - theme: alt
      text: GitHub
      link: https://github.com/justpilot/billomat-php-api

features:
  - title: Typed end to end
    details: final readonly models, enums for every fixed value set, exact Money amounts instead of float. Unknown fields are kept in $extra.
  - title: Immutable options and typed filters
    details: Named arguments, with*() withers, Clear::Field to empty a field, InvoiceFilter & co. with OrderBy and auto-pagination.
  - title: Any PSR-18 client
    details: Symfony, Guzzle or whatever you use. Runtime dependencies are PSR interfaces only.
  - title: Resilient and observable
    details: Retries for 429 and transient errors, classified exceptions, optional PSR-3 logging and PSR-14 events.
  - title: Webhooks
    details: Parse incoming Billomat webhooks into the matching model, all 83 events included.
  - title: Unofficial
    details: Independently developed and maintained; not affiliated with Billomat GmbH.
---

```bash
composer require justpilot/billomat-php-api
```

```php
$billomat = BillomatClient::create(billomatId: 'mycompany', apiKey: getenv('BILLOMAT_API_KEY'));

$invoice = $billomat->invoices->getOrFail(42);
echo $invoice->totalGross?->format(2, ',', '.');
```

::: info Documentation language
The documentation is being translated to English for 4.0; most pages are still German.
:::
