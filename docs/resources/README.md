# Resources

Every Billomat resource is a property on `BillomatClient`; sub-resources hang off their parent (`$billomat->invoices->payments`). Each page lists the methods, fields and quirks of one resource (most pages are still German).

## Master data

| Resource | Access | Docs |
|---|---|---|
| Clients | `$billomat->clients`, `->tags`, `->properties`, `->propertyValues` | [clients.md](clients.md), [client-tags.md](client-tags.md) |
| Contacts | `$billomat->contacts` | [contacts.md](contacts.md) |
| Suppliers | `$billomat->suppliers`, `->tags`, `->properties`, `->propertyValues` | [suppliers.md](suppliers.md) |
| Articles | `$billomat->articles`, `->tags`, `->properties`, `->propertyValues` | [articles.md](articles.md) |
| Property definitions | `->properties` on clients, suppliers, articles, incomings and users | [properties.md](properties.md) |

## Outgoing documents

| Resource | Access | Docs |
|---|---|---|
| Invoices | `$billomat->invoices`, `->items`, `->payments`, `->comments`, `->tags` | [invoices.md](invoices.md), [items](invoice-items.md), [payments](invoice-payments.md), [comments](invoice-comments.md), [tags](invoice-tags.md) |
| Recurring invoices | `$billomat->recurrings`, `->items`, `->tags`, `->emailReceivers` | [recurrings.md](recurrings.md) |
| Offers | `$billomat->offers`, `->items`, `->comments`, `->tags` | [offers.md](offers.md) |
| Confirmations | `$billomat->confirmations`, `->items`, `->comments`, `->tags` | [confirmations.md](confirmations.md) |
| Delivery notes | `$billomat->deliveryNotes`, `->items`, `->comments`, `->tags` | [delivery-notes.md](delivery-notes.md) |
| Credit notes | `$billomat->creditNotes`, `->items`, `->payments`, `->comments`, `->tags` | [credit-notes.md](credit-notes.md) |
| Reminders | `$billomat->reminders`, `->items`, `->tags` | [reminders.md](reminders.md) |
| Letters | `$billomat->letters`, `->comments`, `->tags` | [letters.md](letters.md) |

## Incoming documents

| Resource | Access | Docs |
|---|---|---|
| Incoming invoices | `$billomat->incomings`, `->payments`, `->comments`, `->tags`, `->properties`, `->propertyValues` | [incomings.md](incomings.md) |
| Incoming categories | `$billomat->incomingCategories` | [incoming-categories.md](incoming-categories.md) |
| Inbox documents | `$billomat->inboxDocuments` | [inbox-documents.md](inbox-documents.md) |

## Account and settings

| Resource | Access | Docs |
|---|---|---|
| Account | `$billomat->account` | [account.md](account.md) |
| Activity feed | `$billomat->activities` | [activities.md](activities.md) |
| Search | `$billomat->search` | [search.md](search.md) |
| Settings | `$billomat->settings` | [settings.md](settings.md) |
| Taxes | `$billomat->taxes` | [taxes.md](taxes.md) |
| Tax-free countries | `$billomat->countryTaxes` | [settings-tax-free-countries.md](settings-tax-free-countries.md) |
| Roles | `$billomat->roles` | [settings-roles.md](settings-roles.md) |
| Templates | `$billomat->templates` | [templates.md](templates.md) |
| E-mail templates, free texts | `$billomat->emailTemplates`, `$billomat->freeTexts` | [settings-email-templates.md](settings-email-templates.md), [settings-free-texts.md](settings-free-texts.md) |
| Lookups (countries, currencies, units, users, reminder texts) | `$billomat->countries`, `currencies`, `units`, `users`, `reminderTexts` | [lookups.md](lookups.md) |
