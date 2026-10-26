# Backward compatibility

The SDK follows [Semantic Versioning](https://semver.org/). This page says what that covers, so you know which code survives a minor update.

## Release phases of 4.0

- **Alpha** (`4.0.0-alpha.*`): the public API may still change.
- **Beta** (`4.0.0-beta.*`): the public API is frozen. Betas only get fixes and additions; a break is only made to fix a bug, and it is listed in the CHANGELOG and in [UPGRADE-4.0.md](../../UPGRADE-4.0.md).
- **Stable** (`4.0.0` and later): breaks only in a new major version.

CI runs [`roave/backward-compatibility-check`](https://github.com/Roave/BackwardCompatibilityCheck) against the latest 4.x tag on every pull request.

## What is covered

Everything public under `Justpilot\Billomat\` that is not marked `@internal`:

- class, interface and enum names, enum cases,
- public methods with their parameter names (named arguments are part of the API), types and return types,
- public properties of read models, filters, options, events and exceptions,
- the exception hierarchy: an error that throws `ValidationException` today won't throw a different class tomorrow.

## What is not covered

- **`@internal` code:** `AbstractApi`, `AbstractSubResourceApi`, the constructors of the sub-resource APIs, `Justpilot\Billomat\Internal\*` and the `FromApi` trait (the `fromApi()` method on the enums is public). Get API objects from `BillomatClient`, never with `new`. For endpoints without an SDK method, use `$billomat->getHttpClient()->request()`.
- **Parameter order of constructors** of read models, options and filters. Many of them are generated from the Billomat field list, and a new field may land in the middle. Pass arguments by name: `new InvoiceCreateOptions(clientId: 1, date: $date)`.
- **Additions**, which may come with any minor release: new classes, methods, optional parameters at the end of a method, read model properties, enum cases and keys in `$extra`. If you `match` over an SDK enum, add a `default` arm, because Billomat extends its value sets from time to time.
- **Message texts** of exceptions and log entries, and the order of log entries and events. Log levels only change in a minor release, with a CHANGELOG entry.
- **Billomat's behaviour.** If Billomat stops sending a field, the property stays but becomes `null`; fields Billomat drops for good are removed in the next major version.
