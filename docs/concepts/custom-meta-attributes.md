<!-- Source: https://www.billomat.com/api/grundlagen/eigene-meta-daten/ + https://www.billomat.com/api/benutzerdefinierte-attribute-filtern/ -->

# Custom metadata

Billomat offers **two** ways to attach your own data to resources. They are easy to confuse because they sound similar, but they work differently:

| Mechanism | What | Use for |
|---|---|---|
| `customfield` | **one** free text slot per record | A quick 1:1 link between a Billomat record and an external system (e.g. your own primary keys). |
| Property / PropertyValue | **defined** custom attributes with name, type and optional default values | Domain extensions such as "favorite color", "CRM ID", "contract number". |

## `customfield` — the free text slot

Available per record on all major resources (clients, invoices, incoming invoices, suppliers, …).

### Reading / writing

```
GET  /api/clients/{id}/customfield
PUT  /api/clients/{id}/customfield
```

Request body for writing:

```xml
<client>
  <customfield>bar</customfield>
</client>
```

### Filtering

List endpoints accept the search parameter `customfield`:

```
GET /api/clients?customfield={search}
```

### In the SDK

When **reading**, the value is available as `?string $customField` on every read model for which Billomat returns it (empty string → `null`):

```php
$client = $billomat->clients->get(42);
$crmId = $client?->customField;
```

To **read and write the field alone**, every API whose records have a `customfield` offers `getCustomField(int $id): ?string` and `setCustomField(int $id, string $value): void` (`GET`/`PUT /{resource}/{id}/customfield`):

```php
$billomat->clients->setCustomField(42, 'crm-4711');
$crmId = $billomat->clients->getCustomField(42);   // 'crm-4711'

$billomat->invoices->items->setCustomField($itemId, 'erp-line-7');
$billomat->clients->setCustomField(42, '');         // empties the field
```

- `getCustomField()` returns `null` for an empty field and throws a `NotFoundException` for a record that doesn't exist.
- `setCustomField()` with `''` clears the field. The write options (`*CreateOptions`/`*UpdateOptions`) have no `customField`; Billomat only changes it through this endpoint.
- Available on all top-level resources with records of their own (clients, contacts, suppliers, articles, invoices, offers, confirmations, credit notes, delivery notes, letters, reminders, recurrings, incomings, inbox documents, taxes, templates, units, reminder texts, country taxes, users) and on the sub-resources items, payments, comments, tags, properties and property values (also `$billomat->reminders->items`).
- Not available on roles, free texts and e-mail templates: Billomat answers their `customfield` endpoint with 405. Countries, currencies and incoming categories have no records with an ID of their own.

To **filter**, pass the parameter via `extra` on the typed filter or in the array: `list(new ClientFilter(extra: ['customfield' => '…']))` or `list(['customfield' => '…'])`.

## Properties and property values — defined custom attributes

Each resource has two API layers:

| API | Purpose |
|---|---|
| `PropertiesApi` (`->properties`) | The **definitions** (name, type, required flag). Maintained in the UI under *Settings → … attributes*. |
| `PropertyValuesApi` (`->propertyValues`) | The **values** per record — references the property by ID. |

In the SDK, both hang off their parent resource:

- `$billomat->clients->properties` + `$billomat->clients->propertyValues`
- `$billomat->articles->properties` + `$billomat->articles->propertyValues`
- `$billomat->suppliers->properties` + `$billomat->suppliers->propertyValues`
- `$billomat->incomings->properties` + `$billomat->incomings->propertyValues`
- `$billomat->users->properties` (definitions only)

See [`docs/resources/properties.md`](../resources/properties.md) for the shared documentation of the properties family.

### Querying values

`propertyValues->list(?int $parentId = null, XPropertyValueFilter|array $filters = [])` returns a flat list of value assignments, optionally limited to one record; `listPage()` and `iterateAll()` take the same arguments. The typed filters (`ArticlePropertyValueFilter`, `ClientPropertyValueFilter`, …) filter by property and value and sort and paginate:

```php
use Justpilot\Billomat\Api\ArticlePropertyValueFilter;

$values = $billomat->articles->propertyValues->list(42);                                                 // all values of article 42
$values = $billomat->articles->propertyValues->list(filters: new ArticlePropertyValueFilter(articlePropertyId: 7)); // all values of property 7
```

### Filtering the parent list by value

List endpoints of the parent resource accept a dynamically named parameter `property<id>`:

```
GET /api/clients?property55=grau
```

means: all clients whose property with ID `55` (e.g. "favorite color") has **exactly** the value `grau`. The filter is case-sensitive and does **no substring match** — "graublau" is not returned.

In the SDK, pass it via `extra` or as an array:

```php
use Justpilot\Billomat\Api\ClientFilter;

$billomat->clients->list(new ClientFilter(extra: ['property55' => 'grau']));
$billomat->clients->list(['property55' => 'grau']);
```

Supported for the five parent resources: **users, articles, clients, suppliers, incoming invoices**.

## Which one to choose?

- **`customfield`** for a single, unstructured piece of external information per record. No UI setup required.
- **Property + property value** for structured, multiple, typed fields that should also be visible in the Billomat UI.
- The two mechanisms are independent — the same record can use both.
