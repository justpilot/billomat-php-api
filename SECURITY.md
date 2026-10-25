# Security

## Handling API credentials

A Billomat API key has the same read and write access as the user it belongs to. Treat it like a password.

- **Never commit it.** Keep credentials out of `composer.json`, the README, tests and any other versioned file. For local tests the SDK uses `.env.test.local`, which is already in `.gitignore`.
- **Load it from the environment** (`getenv('BILLOMAT_API_KEY')`) or a secret manager (Vault, AWS Secrets Manager, Doppler, …). The scripts under `examples/` show the pattern.
- **Separate keys per environment** (sandbox, staging, production) and rotate them when people leave.
- **`X-AppId` / `X-AppSecret` only if you need them.** These headers are for registered third-party apps. A regular Billomat account only needs the API key (`X-BillomatApiKey`). The SDK sends the app headers only when both values are set.

## Logging and error output

- `HttpException` and its subclasses keep Billomat's raw response body in `getResponseBody()`. It helps with debugging but can contain customer data, so don't write it unfiltered to shared logs.
- The SDK's own PSR-3 logging (`logger:` on `BillomatClient`) never logs headers, query strings or bodies, only method, path, status, attempt and timing.
- The PSR-14 events (`events:`) carry the full PSR-7 request, **including the `X-BillomatApiKey` header**. Mask it before you log requests from event listeners.
- The SDK sends the API key only as a header, never in URLs.

## Supported versions

| Version | Supported |
|---|---|
| 4.x | Yes (in development, pre-releases) |
| 3.x | Security fixes until 4.0.0 is released |
| < 3.0 | No |

Security fixes are released as patch releases of the latest minor version.

## Reporting a vulnerability

Please **don't** open a public GitHub issue for security findings. Email the maintainer instead:

- **Email:** `dimitri@justpilot.io`
- **Subject:** `[Security] billomat-php-api: <short description>`

Please include:

- the affected code path (file and function),
- a minimal reproduction,
- the possible impact,
- a suggested fix, if you have one.

We usually confirm receipt within a few working days and then coordinate the fix and the disclosure date with you.

## Out of scope

- Vulnerabilities in the Billomat API itself: please report them to [Billomat](https://www.billomat.com/).
- Vulnerabilities in dependencies (PSR-18 clients, `php-http/discovery`, …): please report them upstream. We raise our constraints once a fix is available.
