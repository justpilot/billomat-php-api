<!-- Source: https://www.billomat.com/api/grundlagen/authentifizierung/ -->

# Authentication

Every API call is authenticated with a personal **API key** that belongs to a specific Billomat user. The API is stateless — there are no sessions; the key must be sent with every request.

## Creating an API key

1. In the Billomat UI, open the user under *Settings → Employees* (*Einstellungen → Mitarbeiter*) and **enable API access**.
2. You can then generate an API key for this user.

The key acts as a password: whoever has it can access the API with the permissions of the associated user.

## Transmission

Billomat accepts the key in two ways:

| Method | Form | Recommendation |
|---|---|---|
| HTTP header | `X-BillomatApiKey: <key>` | Default — easier to filter from logs than URLs, no risk via `Referer` headers. |
| Query parameter | `?api_key=<key>` | Only for ad-hoc tests; ends up in server logs and browser history. |

This SDK uses the **header** only (see `src/Http/BillomatHttpClient.php`).

## Registered apps (`X-AppId`, `X-AppSecret`)

If the calling system is registered as an app in Billomat under *Settings → Administration → Apps* (*Einstellungen → Administration → Apps*), send the app headers as well:

| Header | Meaning |
|---|---|
| `X-AppId` | ID of the registered app |
| `X-AppSecret` | Secret key of the app |

Without these headers, calls count as an "unregistered app" and share the smaller plan quota (see [Errors and rate limits](errors-and-rate-limits.md)).

## Configuration in the SDK

`BillomatClient::create()` (or `BillomatConfig` directly) takes all three values:

```php
$billomat = BillomatClient::create(
    billomatId: 'mycompany',
    apiKey: '…',
    appId: '…',       // optional
    appSecret: '…',   // optional
);
```

From these, `BillomatHttpClient` sets on every request:

- `X-BillomatApiKey: <apiKey>`
- `X-AppId: <appId>` and `X-AppSecret: <appSecret>` (only when both are set)

## Security notes

- **Never** commit API keys to version control — `.env.test.local` is gitignored, and production keys belong in a secret store.
- For the same reason: no keys in URL parameters (server/proxy logs).
- Always use HTTPS (see [API security](api-security.md)).
- If you need read-only access, use a separate user with restricted roles — the key inherits that user's permissions.
