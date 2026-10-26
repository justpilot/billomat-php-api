<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionObject;
use ReflectionProperty;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const PHP_URL_PATH;

/**
 * Prüft `get()` und `getOrFail()` auf allen Ressourcen und Sub-Ressourcen von {@see BillomatClient}.
 */
#[CoversNamespace('Justpilot\\Billomat\\Api')]
#[UsesClass(BillomatClient::class)]
#[UsesNamespace('Justpilot\\Billomat\\Config')]
#[UsesNamespace('Justpilot\\Billomat\\Http')]
#[UsesNamespace('Justpilot\\Billomat\\Exception')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
final class GetOrFailTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function apis(): iterable
    {
        $billomat = self::client(new MockResponse('', ['http_code' => 404]));

        foreach (new ReflectionObject($billomat)->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $api = $property->getValue($billomat);
            if (!\is_object($api)) {
                continue;
            }
            if (method_exists($api, 'getOrFail')) {
                yield $property->name => [$property->name, ''];
            }
            // Sub-Ressourcen hängen als Property-Hooks an der Eltern-API.
            foreach (new ReflectionObject($api)->getProperties(ReflectionProperty::IS_PUBLIC) as $sub) {
                $subApi = $sub->getValue($api);
                if ($sub->hasHooks() && \is_object($subApi) && method_exists($subApi, 'getOrFail')) {
                    yield $property->name.'->'.$sub->name => [$property->name, $sub->name];
                }
            }
        }
    }

    #[Test]
    #[DataProvider('apis')]
    public function getOrFailThrowsWhereGetReturnsNull(string $resource, string $subResource): void
    {
        $billomat = self::client(new MockResponse('{"errors":{"error":"Not found"}}', ['http_code' => 404]));
        $api = $this->api($billomat, $resource, $subResource);

        $id = 'incomingCategories' === $resource ? 'abc' : 7;
        $getOrFail = new ReflectionMethod($api, 'getOrFail');

        try {
            $getOrFail->invoke($api, $id);
            self::fail('getOrFail() hat keine NotFoundException geworfen.');
        } catch (NotFoundException $e) {
            self::assertSame(404, $e->getStatusCode());
            self::assertMatchesRegularExpression('#^Billomat responded with HTTP 404 to GET /[a-z-]+/'.$id.'\.$#', $e->getMessage());
        }
    }

    #[Test]
    #[DataProvider('apis')]
    public function getOrFailReturnsWhatGetReturns(string $resource, string $subResource): void
    {
        // Envelope-Schlüssel aus dem Pfad: /credit-notes/7 → credit-note, /taxes/7 → tax, /incoming-categories/x → incoming-category.
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            $slug = explode('/', (string) parse_url($url, PHP_URL_PATH))[2] ?? '';
            $key = (string) preg_replace(['/ies$/', '/(x)es$/', '/s$/'], ['y', '$1', ''], $slug);

            return new MockResponse((string) json_encode([$key => ['id' => '7', 'unit_price' => '1', 'amount' => '1']]));
        });
        $billomat = new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));
        $api = $this->api($billomat, $resource, $subResource);
        $id = 'incomingCategories' === $resource ? 'abc' : 7;

        $expected = new ReflectionMethod($api, 'get')->invoke($api, $id);
        $actual = new ReflectionMethod($api, 'getOrFail')->invoke($api, $id);

        self::assertNotNull($expected);
        self::assertEquals($expected, $actual);
    }

    #[Test]
    #[DataProvider('apis')]
    public function getThrowsOnUnexpectedEnvelope(string $resource, string $subResource): void
    {
        // 200 ohne passende Hülle ist kein „nicht gefunden“: get() darf hier nicht null liefern.
        $billomat = self::client(new MockResponse('{"something-else":{"id":"7"}}'));
        $api = $this->api($billomat, $resource, $subResource);
        $id = 'incomingCategories' === $resource ? 'abc' : 7;

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageMatches('/^Unexpected response from Billomat when fetching [a-z ]+\.$/');

        new ReflectionMethod($api, 'get')->invoke($api, $id);
    }

    #[Test]
    public function getOrFailReturnsTheResource(): void
    {
        $billomat = self::client(new MockResponse('{"client":{"id":"7","name":"ACME"}}'));

        $client = $billomat->clients->getOrFail(7);

        self::assertSame('ACME', $client->name);
    }

    #[Test]
    public function getOrFailOnSubResourcesReturnsTheResource(): void
    {
        $billomat = self::client(new MockResponse('{"invoice-comment":{"id":"3","comment":"Hallo"}}'));

        $comment = $billomat->invoices->comments->getOrFail(3);

        self::assertSame('Hallo', $comment->comment);
    }

    #[Test]
    public function messageNamesThePathOfTheResource(): void
    {
        $billomat = self::client(new MockResponse('', ['http_code' => 404]));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Billomat responded with HTTP 404 to GET /invoice-comments/3.');

        (void) $billomat->invoices->comments->getOrFail(3);
    }

    private static function client(MockResponse $response): BillomatClient
    {
        return new BillomatClient(
            new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'),
            new Psr18Client(new MockHttpClient($response)),
        );
    }

    private function api(BillomatClient $billomat, string $resource, string $subResource): object
    {
        $api = new ReflectionProperty($billomat, $resource)->getValue($billomat);
        self::assertIsObject($api);
        if ('' === $subResource) {
            return $api;
        }
        $sub = new ReflectionProperty($api, $subResource)->getValue($api);
        self::assertIsObject($sub);

        return $sub;
    }
}
