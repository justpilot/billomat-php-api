# Backward compatibility

The SDK follows [Semantic Versioning](https://semver.org/). This page says what that covers, so you know which code survives a minor update.

From 4.0.0 on, breaking changes only come with a new major version. Minor releases add features, patch releases fix bugs. The breaks between the 4.0 betas and 4.0.0 are listed in [UPGRADE-4.0.md](../../UPGRADE-4.0.md).

CI runs [`roave/backward-compatibility-check`](https://github.com/Roave/BackwardCompatibilityCheck) against the latest 4.x tag on every pull request.

## What is covered

Everything public under `Justpilot\Billomat\` that is not marked `@internal`:

- class, interface and enum names, enum cases,
- public methods with their parameter names (named arguments are part of the API), types and return types, including the methods an `*Api` class inherits from the `@internal` base classes (`$billomat->invoices->comments->get()` is covered even though it is declared in `AbstractSubResourceApi`),
- public properties of read models, filters, options, events and exceptions,
- the exception hierarchy: an error that throws `ValidationException` today won't throw a different class tomorrow.

## What is not covered

- **`@internal` code:** `AbstractApi`, `AbstractSubResourceApi`, the constructors of all `*Api` classes, of the events (`Justpilot\Billomat\Event\*`), of `Page`/`PageInfo` and of `HttpException`, `WebhookEvent::envelopeKey()`, `Justpilot\Billomat\Internal\*`, the `FromApi` trait (the `fromApi()` method on the enums is public) and the `$sleep` test hook of `BillomatHttpClient`'s constructor. The SDK creates events, pages and exceptions itself; their properties and getters are covered, constructing them is not. Get API objects from `BillomatClient`, never with `new`. For endpoints without an SDK method, use `$billomat->getHttpClient()->request()`.
- **Own implementations of `BillomatHttpClientInterface`.** Calling it is covered; a minor release may add methods to the interface.
- **Parameter order of constructors** of read models, options and filters. Many of them are generated from the Billomat field list, and a new field may land in the middle. Pass arguments by name: `new InvoiceCreateOptions(clientId: 1, date: $date)`.
- **Additions**, which may come with any minor release: new classes, methods, optional parameters at the end of a method, read model properties, enum cases and keys in `$extra`. If you `match` over an SDK enum, add a `default` arm, because Billomat extends its value sets from time to time.
- **Message texts** of exceptions and log entries, and the order of log entries and events. Log levels only change in a minor release, with a CHANGELOG entry.
- **Billomat's behaviour.** If Billomat stops sending a field, the property stays but becomes `null`; fields Billomat drops for good are removed in the next major version.
- **`@experimental` code**, see below.

## Experimental API

Billomat sometimes publishes endpoints before they are final. The SDK supports them early, but marks the classes `@experimental` as long as Billomat calls them preliminary. Experimental classes may change in a minor release (renamed fields, changed types, removed methods) when Billomat changes the endpoint; each change gets a CHANGELOG entry. Once Billomat declares the endpoint final, a minor release drops the marker and the class falls under the promise above.

Currently experimental, because Billomat's documentation says the endpoints only become binding on 2027-01-01:

- [Client delivery addresses](../resources/client-delivery-addresses.md): `ClientsApi::$deliveryAddresses`, `ClientDeliveryAddressesApi`, `ClientDeliveryAddress`, `ClientDeliveryAddressCreateOptions`, `ClientDeliveryAddressUpdateOptions`.

The `clientDeliveryAddressId` field on invoices and recurring invoices is a plain ID and covered as usual.
