<!-- Source: https://www.billomat.com/api/rechnungen/kommentare/ -->

# Invoice Comments (Rechnungskommentare)

API wrapper for invoice comments at `/invoice-comments`.

## Access

```php
$billomat->invoices->comments
```

The generic `Justpilot\Billomat\Api\CommentsApi`, typed for `InvoiceComment`, `InvoiceCommentCreateOptions` and `InvoiceCommentActionKey`.

## Overview

For each invoice, Billomat records a stream of comments: some are system-generated (status changes, received payments, emails sent), some are written manually by users. Both kinds live in the same resource and differ only in the `actionkey` field:

- **System comments** have `actionkey` set (`COMPLETE`, `PAYMENT`, `EMAIL`, …) and `user_id = null`.
- **Manual comments** usually have no `actionkey` and a specific `user_id`.

## Endpoints

| Method | HTTP | Path |
|---|---|---|
| `list($invoiceId, $actionKeys?, $filters?)` | GET | `/invoice-comments?invoice_id={id}` |
| `listPage(…)` / `iterateAll(…, $pageSize?)` | GET | `/invoice-comments?invoice_id={id}` (paginated) |
| `get($id)` / `getOrFail($id)` | GET | `/invoice-comments/{id}` |
| `create($invoiceId, $options)` | POST | `/invoice-comments` |
| `delete($id)` | DELETE | `/invoice-comments/{id}` |

There is intentionally no `list()` without `invoice_id`; Billomat requires the filter.

## Methods

### `list(int $invoiceId, ?array $actionKeys = null, SubResourceFilter|array $filters = []): list<InvoiceComment>`

Lists all comments of an invoice. You can optionally filter by `InvoiceCommentActionKey` values; the list is sent to Billomat comma-separated. Passing a different enum throws an `InvalidArgumentException`.

```php
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\SubResourceFilter;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

// All comments
$all = $billomat->invoices->comments->list(98765);

// Only payment and dunning entries
$financial = $billomat->invoices->comments->list(
    98765,
    [InvoiceCommentActionKey::PAYMENT, InvoiceCommentActionKey::DUNNING],
);

// Newest first, 20 per page
$latest = $billomat->invoices->comments->list(
    98765,
    filters: new SubResourceFilter(orderBy: OrderBy::desc('created'), perPage: 20),
);
```

`SubResourceFilter` (`orderBy`, `page`, `perPage`, `extra`) sorts and paginates. `listPage()` and `iterateAll()` take the same arguments (`iterateAll()` additionally `$pageSize`); see [Pagination](../advanced/pagination.md).

### `get(int $id): ?InvoiceComment` / `getOrFail(int $id): InvoiceComment`

`get()` returns `null` on 404; `getOrFail()` throws a `NotFoundException` instead.

### `create(int $invoiceId, InvoiceCommentCreateOptions $options): InvoiceComment`

```php
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;

$comment = $billomat->invoices->comments->create(
    98765,
    new InvoiceCommentCreateOptions(
        comment: 'Client called for clarification; call back scheduled for 2026-06-05.',
    ),
);
```

`actionkey` is optional and should be left empty for manual comments; Billomat fills it automatically for system actions.

### `delete(int $id): void`

Deletes a comment. Errors are thrown as exceptions.

## Write model: `InvoiceCommentCreateOptions`

Constructor: `new InvoiceCommentCreateOptions(string $comment, …)`. The object is immutable (`final readonly`) and has a `with*()` method per field. The invoice ID is the first argument of `create()`, which adds it to the payload as `invoice_id`.

| Property | Billomat field | Type | Notes |
|---|---|---|---|
| `comment` | `comment` | `string` | Required (constructor) |
| `actionkey` | `actionkey` | `?InvoiceCommentActionKey` | Rarely set manually |
| `public` | `public` | `?bool` | Visible in the client's activity list (Billomat default: `false`) |

`toArray()` drops `null` values.

## Read model: `InvoiceComment`

`final readonly class InvoiceComment`.

| Property | Type | Notes |
|---|---|---|
| `id` | `int` | |
| `invoiceId` | `int` | |
| `comment` | `?string` | |
| `created` | `?\DateTimeImmutable` | |
| `userId` | `?int` | `null` for system comments |
| `actionkey` | `?InvoiceCommentActionKey` | `null` for an unknown or missing value |
| `actionkeyRaw` | `?string` | Original API string, even if the enum doesn't know the value |
| `public` | `?bool` | |
| `byClient` | `?bool` | |
| `emailId` | `?int` | |
| `clientId` | `?int` | |
| `customfield` | `?string` | Free extra field, see [custom meta attributes](../concepts/custom-meta-attributes.md) |
| `extra` | `array<string, mixed>` | Fields the SDK doesn't map (yet), raw; see [models](../concepts/models.md) |

## Enum: `InvoiceCommentActionKey`

[`InvoiceCommentActionKey`](../../src/Model/Enum/InvoiceCommentActionKey.php) covers the action keys observed in API output:

| Case | Wire value |
|---|---|
| `CREATE` | `CREATE` |
| `EDIT` | `EDIT` |
| `OPEN` | `OPEN` |
| `COMPLETE` | `COMPLETE` |
| `CANCEL` | `CANCEL` |
| `UNCANCEL` | `UNCANCEL` |
| `CHANGE_STATUS` | `CHANGE_STATUS` |
| `PAYMENT` | `PAYMENT` |
| `EMAIL` | `EMAIL` |
| `MAIL` | `MAIL` |
| `DUNNING` | `DUNNING` |
| `COMMENT` | `COMMENT` |

Billomat doesn't document a closed list. If new values show up, the SDK parses `actionkey` to `null` and fills `actionkeyRaw` with the original string, so nothing is lost.

<!-- json-examples:start (generated by tools/generate-json-examples.php, run `composer docs:json`) -->
## JSON on the wire

Billomat's API documentation only shows XML. These are the JSON bodies the SDK sends and receives: requests as the SDK serializes them, responses recorded in the Billomat sandbox and anonymized ([contract fixtures](../../tests/Fixtures/Contract)). Billomat sends every value as a string; the read models cast them (see [Read models](../concepts/models.md)).

::: code-group
<<< @/snippets/json/invoice-comments.create.json [POST /invoice-comments]
<<< @/../tests/Fixtures/Contract/invoice-comments/get.json [GET /invoice-comments/{id} (response)]
<<< @/../tests/Fixtures/Contract/invoice-comments/list.json [GET /invoice-comments (response)]
:::
<!-- json-examples:end -->

## Pitfalls

- **`invoice_id` is required.** Without the filter Billomat responds with 400; `list()` sets it automatically.
- **Don't fake system comments.** `actionkey` is primarily API output. Setting a value like `PAYMENT` manually can distort the activity view in the Billomat UI.
- **`delete()` also deletes system comments.** Be careful when cleaning up: system entries are part of the audit trail.

## End-to-end example

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: getenv('BILLOMAT_API_KEY'),
);

$invoiceId = 98765;

// 1) Add a manual comment
$billomat->invoices->comments->create(
    $invoiceId,
    new InvoiceCommentCreateOptions(
        comment: 'Client asks for a 14-day extension.',
    ),
);

// 2) Show the full history
foreach ($billomat->invoices->comments->list($invoiceId) as $c) {
    printf(
        "[%s] %s: %s\n",
        $c->created?->format('Y-m-d H:i') ?? '???',
        $c->actionkey?->value ?? ($c->actionkeyRaw ?? 'manual'),
        $c->comment ?? '',
    );
}

// 3) Only the dunning history
$dunning = $billomat->invoices->comments->list(
    $invoiceId,
    [InvoiceCommentActionKey::DUNNING],
);
printf("Dunning entries: %d\n", count($dunning));
```
