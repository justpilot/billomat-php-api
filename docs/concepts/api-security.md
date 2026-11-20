<!-- Source: https://www.billomat.com/api/grundlagen/api-sicherheit/ -->

# API security

The Billomat API is available at `https://{billomatId}.billomat.net/api/`. **HTTPS** is available on every plan and should always be used for production systems. Plain HTTP would expose request data and the API key on the network.

## What this SDK guarantees

- **HTTPS base URI** — the default `BillomatConfig::$baseUri` is `https://%s.billomat.net/api/`. You can only change it by passing your own `baseUri` explicitly (meant for local mock servers in tests); don't point it at plain HTTP in production.
- **API key as a header** — `X-BillomatApiKey` (and optionally `X-AppId`/`X-AppSecret`) are sent as request headers, never in the URL. Keys therefore do **not** end up in server access logs, browser history or `Referer` headers (see [Authentication](authentication.md)).
- **No secrets in SDK logs** — the PSR-3 log context contains no headers, query strings or bodies (see [Configuration](../configuration.md#example-logging-requests-and-responses)). PSR-14 events do carry the full request including the API key header; mask it before logging.
- **PSR-18 client as transport** — TLS verification is handled by the client in use (Symfony `HttpClient`, Guzzle, …). Both use the system trust store and verify certificates by default. TLS verification can be disabled there, but shouldn't be — if you pass your own client, you are responsible for its TLS configuration.

## Recommendations for callers

- Treat **API keys** like passwords: not in code, not in Git, not in logs. Locally in `.env.test.local` (gitignored), in production in a secret store.
- Create a **dedicated Billomat user** per integration and grant only the roles it really needs. The API key inherits its user's roles.
- If a key is compromised, **revoke** it in the Billomat UI (delete the old key and generate a new one). Other keys are not affected.
- Additionally protect webhook endpoints with **HTTP Basic Auth** — Billomat supports this per webhook configuration (see [Webhooks](webhooks.md)).
- For apps, send the app headers `X-AppId`/`X-AppSecret` — they identify your app and give it its own rate limit quota (see [Errors and rate limits](errors-and-rate-limits.md)).

## What the API does **not** protect

- **No IP allowlist** — the key alone authenticates, no matter where the request comes from. If you want to restrict that, terminate TLS upstream and filter there.
- **No scopes or per-use-case tokens** — the API key always has all permissions of its user. Separate access by using separate users with different roles.
- **No rotation** via the API — keys can only be replaced manually in the UI.
