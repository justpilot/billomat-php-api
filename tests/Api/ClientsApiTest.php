<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\ClientUpdateOptions;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\TaxRule;
use Justpilot\Billomat\Model\Enum\ValueType;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Pagination\Page;
use Justpilot\Billomat\Pagination\PageInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;
use const PHP_URL_QUERY;

#[CoversClass(ClientsApi::class)]
#[CoversClass(ClientCreateOptions::class)]
#[CoversClass(ClientUpdateOptions::class)]
#[CoversClass(Client::class)]
#[CoversClass(Page::class)]
#[CoversClass(PageInfo::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(ClientFilter::class)]
#[UsesClass(OrderBy::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class ClientsApiTest extends TestCase
{
    #[Test]
    public function itFetchesOwnAccountViaClientsMyself(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'client' => [
                    'id' => 12345,
                    'name' => 'Mein Account',
                    'client_number' => 'ACC1',
                    'email' => 'info@example.com',
                    'country_code' => 'DE',
                    'customfield' => 'external-acc-id',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new ClientsApi($http);

        $me = $api->getMyself();

        self::assertSame(12345, $me->id);
        self::assertSame('Mein Account', $me->name);
        self::assertSame('ACC1', $me->clientNumber);
        self::assertSame('info@example.com', $me->email);
        self::assertSame('DE', $me->countryCode);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients/myself',
            $captured['url']
        );
    }

    #[Test]
    public function itListsClientsAndPassesFilters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'clients' => [
                    'client' => [
                        [
                            'id' => 1,
                            'name' => 'Client A GmbH',
                            'client_number' => 'C001',
                            'street' => 'Musterstraße 1',
                            'zip' => '12345',
                            'city' => 'Berlin',
                            'country_code' => 'DE',
                            'first_name' => 'Max',
                            'last_name' => 'Mustermann',
                            'email' => 'max@example.com',
                            'debitor_account_number' => 4711,
                        ],
                        [
                            'id' => 2,
                            'name' => 'Client B AG',
                            'client_number' => 'C002',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new ClientsApi($http);

        $filters = ['per_page' => 50];

        $clients = $api->list($filters);

        self::assertIsArray($clients);
        self::assertCount(2, $clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        $first = $clients[0];
        self::assertSame(1, $first->id);
        self::assertSame('Client A GmbH', $first->name);
        self::assertSame('C001', $first->clientNumber);
        self::assertSame('Musterstraße 1', $first->street);
        self::assertSame('12345', $first->zip);
        self::assertSame('Berlin', $first->city);
        self::assertSame('DE', $first->countryCode);
        self::assertSame('Max', $first->firstName);
        self::assertSame('Mustermann', $first->lastName);
        self::assertSame('max@example.com', $first->email);
        self::assertSame(4711, $first->debitorAccountNumber);

        // Request prüfen
        self::assertSame('GET', $captured['method']);

        $url = $captured['url'];
        $parts = parse_url($url);

        self::assertSame('/api/clients', $parts['path'] ?? null);

        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        self::assertSame(50, (int) ($query['per_page'] ?? 0));
    }

    #[Test]
    public function itSendsTypedFilterAsQuery(): void
    {
        $urls = [];
        $mock = new MockHttpClient(static function (string $method, string $url) use (&$urls): MockResponse {
            $urls[] = $url;

            return new MockResponse('{"clients":{"client":[],"@page":"1","@per_page":"10","@total":"0"}}', ['http_code' => 200]);
        });
        $api = new ClientsApi(new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key')));
        $filter = new ClientFilter(name: 'ACME', invoiceId: [1, 2], orderBy: OrderBy::desc('date')->thenAsc('name'), perPage: 10);

        (void) $api->list($filter);
        (void) $api->listPage($filter->withPage(3));
        iterator_to_array($api->iterateAll($filter->withPage(9), pageSize: 25));

        // `+` bleibt wörtlich, das Komma wird kodiert.
        self::assertSame('name=ACME&invoice_id=1%2C2&order_by=date+DESC%2Cname+ASC&per_page=10', parse_url($urls[0], PHP_URL_QUERY));
        self::assertSame('name=ACME&invoice_id=1%2C2&order_by=date+DESC%2Cname+ASC&page=3&per_page=10', parse_url($urls[1], PHP_URL_QUERY));
        // iterateAll() überschreibt page/per_page.
        self::assertSame('name=ACME&invoice_id=1%2C2&order_by=date+DESC%2Cname+ASC&page=1&per_page=25', parse_url($urls[2], PHP_URL_QUERY));
    }

    #[Test]
    public function itGetsSingleClientById(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'client' => [
                    'id' => 123,
                    'name' => 'Single Client GmbH',
                    'client_number' => 'SC-123',
                    'first_name' => 'Erika',
                    'last_name' => 'Musterfrau',
                    'email' => 'erika@example.com',
                    'debitor_account_number' => 9001,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new ClientsApi($http);

        $client = $api->get(123);

        self::assertInstanceOf(Client::class, $client);
        self::assertSame(123, $client->id);
        self::assertSame('Single Client GmbH', $client->name);
        self::assertSame('SC-123', $client->clientNumber);
        self::assertSame('Erika', $client->firstName);
        self::assertSame('Musterfrau', $client->lastName);
        self::assertSame('erika@example.com', $client->email);
        self::assertSame(9001, $client->debitorAccountNumber);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients/123',
            $captured['url']
        );
    }

    #[Test]
    public function itCreatesANewClientViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Server-Response mit gesetzter ID und zurückgespiegelten Daten
            $body = json_encode([
                'client' => [
                    'id' => 999,
                    'name' => 'New Client GmbH',
                    'client_number' => 'C-100',
                    'first_name' => 'Max',
                    'last_name' => 'Mustermann',
                    'salutation' => 'Herr',
                    'email' => 'new@example.com',
                    'country_code' => 'DE',
                    'debitor_account_number' => 4711,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new ClientsApi($http);

        $opts = new ClientCreateOptions(
            name: 'New Client GmbH',
            clientNumber: 'C-100',
            countryCode: 'DE',
            firstName: 'Max',
            lastName: 'Mustermann',
            salutation: 'Herr',
            email: 'new@example.com',
            debitorAccountNumber: 4711,
        );

        $created = $api->create($opts);

        self::assertSame(999, $created->id);
        self::assertSame('New Client GmbH', $created->name);
        self::assertSame('C-100', $created->clientNumber);
        self::assertSame('Max', $created->firstName);
        self::assertSame('Mustermann', $created->lastName);
        self::assertSame('Herr', $created->salutation);
        self::assertSame('new@example.com', $created->email);
        self::assertSame('DE', $created->countryCode);
        self::assertSame(4711, $created->debitorAccountNumber);

        // Request prüfen
        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients',
            $captured['url']
        );

        $options = $captured['options'] ?? [];
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body'])) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('client', $payload);

        $clientPayload = $payload['client'];

        self::assertSame('New Client GmbH', $clientPayload['name'] ?? null);
        self::assertSame('Max', $clientPayload['first_name'] ?? null);
        self::assertSame('Mustermann', $clientPayload['last_name'] ?? null);
        self::assertSame('Herr', $clientPayload['salutation'] ?? null);
        self::assertSame('C-100', $clientPayload['client_number'] ?? null);
        self::assertSame('new@example.com', $clientPayload['email'] ?? null);
        self::assertSame('DE', $clientPayload['country_code'] ?? null);
        self::assertSame(4711, $clientPayload['debitor_account_number'] ?? null);

        // id darf im Payload nicht gesetzt sein
        self::assertArrayNotHasKey('id', $clientPayload);
    }

    #[Test]
    public function itUpdatesClientViaPutAndSendsWrapperPayload(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'client' => [
                    'id' => 123,
                    'name' => 'Die super Musterfirma',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new ClientsApi($http);

        $opts = new ClientUpdateOptions(name: 'Die super Musterfirma');

        $updated = $api->update(123, $opts);

        self::assertSame(123, $updated->id);
        self::assertSame('Die super Musterfirma', $updated->name);

        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients/123',
            $captured['url']
        );

        $options = $captured['options'] ?? [];

        // Payload robust lesen: options['json'] oder options['body']
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body']) && '' !== $options['body']) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload, 'Expected JSON payload array (options[json] or decoded options[body]).');
        self::assertArrayHasKey('client', $payload);
        self::assertIsArray($payload['client']);

        self::assertSame('Die super Musterfirma', $payload['client']['name'] ?? null);
    }

    #[Test]
    public function updateOptionsSerializesAllNewWritableFields(): void
    {
        $opts = new ClientUpdateOptions(
            name: 'Beispiel GmbH',
            clientNumber: 'C-9001',
            number: 9001,
            numberPre: 'KU-',
            numberLength: 5,
            locale: 'de_DE',
            taxRule: TaxRule::COUNTRY,
            netGross: NetGross::NET,
            currencyCode: 'EUR',
            priceGroup: 2,
            dunningRun: true,
            reduction: 5.0,
            discountRateType: ValueType::ABSOLUTE,
            discountRate: 2.5,
            discountDaysType: ValueType::ABSOLUTE,
            discountDays: 7.0,
            dueDaysType: ValueType::ABSOLUTE,
            dueDays: 14,
            reminderDueDaysType: ValueType::SETTINGS,
            reminderDueDays: 10,
            offerValidityDaysType: ValueType::SETTINGS,
            offerValidityDays: 30,
            bankAccountOwner: 'Max Mustermann',
            bankSwift: 'COBADEFFXXX',
            bankIban: 'DE89370400440532013000',
            sepaMandate: 'MD-001',
            sepaMandateDate: '2026-01-15',
            defaultPaymentTypes: [InvoicePaymentType::CASH, InvoicePaymentType::BANK_TRANSFER],
        );

        $payload = $opts->toArray();

        self::assertSame('Beispiel GmbH', $payload['name']);
        self::assertSame('de_DE', $payload['locale']);
        self::assertSame('COUNTRY', $payload['tax_rule']);
        self::assertSame('NET', $payload['net_gross']);
        self::assertSame('EUR', $payload['currency_code']);
        self::assertSame(2, $payload['price_group']);
        self::assertSame(1, $payload['dunning_run']);
        self::assertSame('C-9001', $payload['client_number']);
        self::assertSame('KU-', $payload['number_pre']);
        self::assertSame(9001, $payload['number']);
        self::assertSame(5, $payload['number_length']);
        self::assertSame('DE89370400440532013000', $payload['bank_iban']);
        self::assertSame('COBADEFFXXX', $payload['bank_swift']);
        self::assertSame('Max Mustermann', $payload['bank_account_owner']);
        self::assertSame('MD-001', $payload['sepa_mandate']);
        self::assertSame('2026-01-15', $payload['sepa_mandate_date']);
        self::assertSame('CASH,BANK_TRANSFER', $payload['default_payment_types']);
        self::assertSame(5.0, $payload['reduction']);
        self::assertSame('ABSOLUTE', $payload['discount_rate_type']);
        self::assertSame(2.5, $payload['discount_rate']);
        self::assertSame('ABSOLUTE', $payload['discount_days_type']);
        self::assertSame(7.0, $payload['discount_days']);
        self::assertSame('ABSOLUTE', $payload['due_days_type']);
        self::assertSame(14, $payload['due_days']);
        self::assertSame('SETTINGS', $payload['reminder_due_days_type']);
        self::assertSame(10, $payload['reminder_due_days']);
        self::assertSame('SETTINGS', $payload['offer_validity_days_type']);
        self::assertSame(30, $payload['offer_validity_days']);

        // archived nicht gesetzt → darf nicht im Payload landen
        self::assertArrayNotHasKey('archived', $payload);
    }

    #[Test]
    public function clientModelHydratesBankSepaAndRevenueFields(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Fixture GmbH',
            'created' => '2026-01-15T08:30:00+01:00',
            'address' => "Fixture GmbH\nMusterstr. 1\n12345 Berlin",
            'client_number' => 'C-100',
            'number_pre' => 'C-',
            'number' => '100',
            'number_length' => '5',
            'bank_iban' => 'DE89370400440532013000',
            'bank_swift' => 'COBADEFFXXX',
            'bank_name' => 'Commerzbank',
            'bank_account_owner' => 'Fixture GmbH',
            'sepa_mandate' => 'MD-001',
            'sepa_mandate_date' => '2026-01-10',
            'default_payment_types' => 'CASH,BANK_TRANSFER',
            'enable_customerportal' => '1',
            'customerportal_url' => 'https://portal.example.com/abc',
            'revenue_gross' => '11900.50',
            'revenue_net' => '10000.42',
            'discount_rate_type' => 'ABSOLUTE',
            'discount_days_type' => 'ABSOLUTE',
            'due_days_type' => 'ABSOLUTE',
            'reminder_due_days_type' => 'SETTINGS',
            'offer_validity_days_type' => 'SETTINGS',
        ];

        $c = Client::fromArray($data);

        self::assertSame(1, $c->id);
        self::assertSame('Fixture GmbH', $c->name);
        self::assertNotNull($c->created);
        self::assertSame('2026-01-15', $c->created->format('Y-m-d'));
        self::assertSame("Fixture GmbH\nMusterstr. 1\n12345 Berlin", $c->address);
        self::assertSame(100, $c->number);
        self::assertSame('C-', $c->numberPre);
        self::assertSame(5, $c->numberLength);
        self::assertSame('DE89370400440532013000', $c->bankIban);
        self::assertSame('COBADEFFXXX', $c->bankSwift);
        self::assertSame('Commerzbank', $c->bankName);
        self::assertSame('Fixture GmbH', $c->bankAccountOwner);
        self::assertSame('MD-001', $c->sepaMandate);
        self::assertNotNull($c->sepaMandateDate);
        self::assertSame('2026-01-10', $c->sepaMandateDate->format('Y-m-d'));
        self::assertSame([InvoicePaymentType::CASH, InvoicePaymentType::BANK_TRANSFER], $c->defaultPaymentTypes);
        self::assertTrue($c->enableCustomerportal);
        self::assertSame('11900.5', $c->revenueGross?->toString());
        self::assertSame('10000.42', $c->revenueNet?->toString());
        self::assertSame(ValueType::ABSOLUTE, $c->discountRateType);
        self::assertSame(ValueType::ABSOLUTE, $c->discountDaysType);
        self::assertSame(ValueType::ABSOLUTE, $c->dueDaysType);
        self::assertSame(ValueType::SETTINGS, $c->reminderDueDaysType);
        self::assertSame(ValueType::SETTINGS, $c->offerValidityDaysType);
    }

    #[Test]
    public function itDeletesClientViaDelete(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            return new MockResponse('', ['http_code' => 200]);
        });

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret-key'));
        $api = new ClientsApi($http);

        $api->delete(42);
        self::assertSame('DELETE', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients/42',
            $captured['url']
        );
    }

    #[Test]
    public function deletePropagatesValidationExceptionWhenClientHasDocuments(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
            $body = json_encode([
                'errors' => [
                    'error' => 'Client cannot be deleted because there are documents assigned.',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 400]);
        });

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret-key'));
        $api = new ClientsApi($http);

        $this->expectException(ValidationException::class);

        $api->delete(42);
    }

    #[Test]
    public function itFetchesAvatarBinary(): void
    {
        $captured = [];
        $pngBytes = "\x89PNG\r\n\x1a\nFAKE";

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured, $pngBytes): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            return new MockResponse($pngBytes, [
                'http_code' => 200,
                'response_headers' => [
                    'content-type' => 'image/png',
                ],
            ]);
        });

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret-key'));
        $api = new ClientsApi($http);

        $binary = $api->avatar(42, 256);

        self::assertSame($pngBytes, $binary);

        self::assertSame('GET', $captured['method']);

        $url = $captured['url'];
        $parts = parse_url($url);

        self::assertSame('/api/clients/42/avatar', $parts['path'] ?? null);

        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        self::assertSame(256, (int) ($query['size'] ?? 0));
    }

    #[Test]
    public function avatarOmitsSizeQueryWhenNotProvided(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            return new MockResponse('PNG', [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'image/png'],
            ]);
        });

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret-key'));
        $api = new ClientsApi($http);

        (void) $api->avatar(7);

        self::assertSame(
            'https://mycompany.billomat.net/api/clients/7/avatar',
            $captured['url']
        );
    }

    #[Test]
    public function listPageReturnsPaginationMetadata(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode([
                'clients' => [
                    'client' => [
                        ['id' => 1, 'name' => 'Acme'],
                        ['id' => 2, 'name' => 'Beta'],
                    ],
                    '@page' => '2',
                    '@per_page' => '2',
                    '@total' => '5',
                ],
            ], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $api = new ClientsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $page = $api->listPage(['page' => 2, 'per_page' => 2]);

        self::assertCount(2, $page->items);
        self::assertSame('Acme', $page->items[0]->name);
        self::assertSame(2, $page->info->page);
        self::assertSame(2, $page->info->perPage);
        self::assertSame(5, $page->info->total);
        self::assertSame(3, $page->info->totalPages());
        self::assertTrue($page->info->hasNextPage());
    }

    #[Test]
    public function iterateAllAutoPaginatesUntilLastPage(): void
    {
        $requests = 0;

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$requests): MockResponse {
            ++$requests;

            // URL parsen statt str_contains (per_page=2 enthält 'page=2' als Substring!)
            $query = [];
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $page = (int) ($query['page'] ?? 1);

            $rows = match ($page) {
                1 => [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']],
                2 => [['id' => 3, 'name' => 'C'], ['id' => 4, 'name' => 'D']],
                3 => [['id' => 5, 'name' => 'E']],
                default => [],
            };

            $body = json_encode([
                'clients' => [
                    'client' => $rows,
                    '@page' => (string) $page,
                    '@per_page' => '2',
                    '@total' => '5',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ClientsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $collected = [];
        foreach ($api->iterateAll(pageSize: 2) as $client) {
            $collected[] = $client->name;
        }

        self::assertSame(['A', 'B', 'C', 'D', 'E'], $collected);
        self::assertSame(3, $requests);
    }

    #[Test]
    public function iterateAllStopsEarlyWhenPageShorterThanPageSize(): void
    {
        // Fallback-Pfad: Endpunkt liefert keine `@total`-Metadaten. Die
        // Iteration muss trotzdem terminieren, wenn die Page weniger Items
        // als `per_page` liefert.
        $requests = 0;

        $mock = new MockHttpClient(static function () use (&$requests): MockResponse {
            ++$requests;

            $rows = match ($requests) {
                1 => [['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']],
                2 => [['id' => 3, 'name' => 'C']], // < perPage -> Abbruch
                default => [],
            };

            // Bewusst keine @page/@per_page/@total -> Fallback greift
            return new MockResponse(
                json_encode(['clients' => ['client' => $rows]], JSON_THROW_ON_ERROR),
                ['http_code' => 200],
            );
        });

        $api = new ClientsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $collected = iterator_to_array($api->iterateAll(pageSize: 2), preserve_keys: false);

        self::assertCount(3, $collected);
        self::assertSame(2, $requests);
    }
}
