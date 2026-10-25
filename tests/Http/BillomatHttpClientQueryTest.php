<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Http;

use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Sichert die Billomat-spezifischen Query-/Header-Quirks im HTTP-Layer ab.
 *
 * Hintergrund (siehe CLAUDE.md): Billomat erwartet:
 *  - Arrays als `key[]=v1&key[]=v2` (PSR-7 kodiert die Klammern als `%5B%5D`, Billomat dekodiert sie wieder)
 *  - `+` literal (z. B. `order_by=date+DESC`), nicht `%2B`
 *  - keine leeren Werte
 */
#[CoversClass(BillomatHttpClient::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
final class BillomatHttpClientQueryTest extends TestCase
{
    /**
     * @param array<string, scalar|array<int|string, scalar|null>|null> $query
     */
    #[Test]
    #[DataProvider('queryStringProvider')]
    public function itBuildsExpectedQueryString(array $query, string $expectedSuffix): void
    {
        $captured = new CapturedRequest();
        $mock = $this->captureMock($captured);

        $client = $this->makeClient($mock);
        $client->request('GET', '/clients', $query);

        self::assertSame(
            'https://mycompany.billomat.net/api/clients'.$expectedSuffix,
            $captured->url
        );
    }

    /**
     * @return iterable<string, array{0: array<string, scalar|array<int|string, scalar|null>|null>, 1: string}>
     */
    public static function queryStringProvider(): iterable
    {
        yield 'leere Query liefert keinen Fragezeichen-Suffix' => [
            [],
            '',
        ];

        yield 'einfaches Schlüssel-Wert-Paar' => [
            ['page' => 1],
            '?page=1',
        ];

        yield 'null-Werte werden auf Top-Level übersprungen' => [
            ['page' => 1, 'foo' => null, 'per_page' => 50],
            '?page=1&per_page=50',
        ];

        yield 'Arrays werden als key[]=v Paare serialisiert' => [
            ['payment_type' => ['CASH', 'BANK_TRANSFER']],
            '?payment_type%5B%5D=CASH&payment_type%5B%5D=BANK_TRANSFER',
        ];

        yield 'null-Elemente in Arrays werden übersprungen' => [
            ['ids' => [1, null, 3]],
            '?ids%5B%5D=1&ids%5B%5D=3',
        ];

        yield 'Plus-Zeichen bleibt literal (date+DESC)' => [
            ['order_by' => 'date+DESC'],
            '?order_by=date+DESC',
        ];

        yield 'bool true wird zu 1' => [
            ['archived' => true],
            '?archived=1',
        ];

        yield 'bool false wird zu 0' => [
            ['archived' => false],
            '?archived=0',
        ];

        yield 'Sonderzeichen werden encodiert' => [
            ['name' => 'Müller & Söhne'],
            '?name=M%C3%BCller%20%26%20S%C3%B6hne',
        ];

        yield 'Float-Werte werden mitgesendet' => [
            ['rate' => 19.0],
            '?rate=19',
        ];

        yield 'Query nur aus null-Werten erzeugt keinen Fragezeichen-Suffix' => [
            ['foo' => null, 'bar' => null],
            '',
        ];
    }

    #[Test]
    public function itSetsContentTypeWhenJsonBodyProvided(): void
    {
        $captured = new CapturedRequest();
        $mock = $this->captureMock($captured);

        $client = $this->makeClient($mock);
        $client->request('POST', '/clients', [], ['client' => ['name' => 'Acme']]);

        $headers = $captured->headers();
        self::assertSame('application/json', $headers['content-type'] ?? null);

        // Symfony's HttpClientTrait normalisiert 'json' zu einem JSON-encodierten Body-String
        self::assertSame('{"client":{"name":"Acme"}}', $captured->body());
    }

    #[Test]
    public function itOmitsContentTypeWhenNoJsonBody(): void
    {
        $captured = new CapturedRequest();
        $mock = $this->captureMock($captured);

        $client = $this->makeClient($mock);
        $client->request('GET', '/clients');

        $headers = $captured->headers();
        self::assertArrayNotHasKey('content-type', $headers);
    }

    #[Test]
    public function itOmitsAppHeadersWhenAppCredentialsAreMissing(): void
    {
        $captured = new CapturedRequest();
        $mock = $this->captureMock($captured);

        // Config ohne appId/appSecret
        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $client = new BillomatHttpClient(new Psr18Client($mock), $config);
        $client->request('GET', '/clients');

        $headers = $captured->headers();
        self::assertSame('secret-key', $headers['x-billomatapikey'] ?? null);
        self::assertArrayNotHasKey('x-appid', $headers);
        self::assertArrayNotHasKey('x-appsecret', $headers);
    }

    #[Test]
    public function itTrimsLeadingSlashFromPath(): void
    {
        $captured = new CapturedRequest();
        $mock = $this->captureMock($captured);

        $client = $this->makeClient($mock);

        // Pfad mit führendem Slash darf nicht zu doppeltem Slash führen
        $client->request('GET', '/clients/42');

        self::assertSame(
            'https://mycompany.billomat.net/api/clients/42',
            $captured->url
        );
    }

    #[Test]
    public function itAcceptsPathWithoutLeadingSlash(): void
    {
        $captured = new CapturedRequest();
        $mock = $this->captureMock($captured);

        $client = $this->makeClient($mock);

        $client->request('GET', 'clients/42');

        self::assertSame(
            'https://mycompany.billomat.net/api/clients/42',
            $captured->url
        );
    }

    #[Test]
    public function itSendsAcceptHeader(): void
    {
        $captured = new CapturedRequest();
        $mock = $this->captureMock($captured);

        $client = $this->makeClient($mock);
        $client->request('GET', '/clients');

        $headers = $captured->headers();
        self::assertSame('application/json', $headers['accept'] ?? null);
        self::assertSame('de-de', $headers['accept-language'] ?? null);
    }

    #[Test]
    public function itSendsTheConfiguredLanguage(): void
    {
        self::assertContains('Accept-Language: en-gb', $this->sentHeaders('en-gb'));
    }

    #[Test]
    public function itOmitsAcceptLanguageWithoutLanguage(): void
    {
        $headers = $this->sentHeaders(null);

        self::assertNotSame([], $headers);
        foreach ($headers as $header) {
            self::assertStringStartsNotWith('Accept-Language:', $header);
        }
    }

    /**
     * @return list<string>
     */
    private function sentHeaders(?string $language): array
    {
        $headers = [];
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$headers): MockResponse {
            foreach ((array) ($options['headers'] ?? []) as $header) {
                $headers[] = \is_string($header) ? $header : '';
            }

            return new MockResponse('{}');
        });
        $client = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig(billomatId: 'mycompany', apiKey: 'k', language: $language));

        $client->request('GET', '/clients');

        return $headers;
    }

    private function captureMock(CapturedRequest $captured): MockHttpClient
    {
        return new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->method = $method;
            $captured->url = $url;
            $captured->options = $options;

            return new MockResponse('{"ok": true}', ['http_code' => 200]);
        });
    }

    private function makeClient(MockHttpClient $mock): BillomatHttpClient
    {
        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            appId: 'app-id',
            appSecret: 'app-secret',
        );

        return new BillomatHttpClient(new Psr18Client($mock), $config);
    }
}
