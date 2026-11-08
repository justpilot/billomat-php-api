<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
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
 * `getCustomField()`/`setCustomField()` (GET/PUT /{resource}/{id}/customfield) auf allen
 * Ressourcen und Sub-Ressourcen von {@see BillomatClient}.
 */
#[CoversNamespace('Justpilot\\Billomat\\Api')]
#[UsesClass(BillomatClient::class)]
#[UsesNamespace('Justpilot\\Billomat\\Config')]
#[UsesNamespace('Justpilot\\Billomat\\Http')]
#[UsesNamespace('Justpilot\\Billomat\\Exception')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
final class CustomFieldTest extends TestCase
{
    /**
     * Alle APIs mit `get(int $id)`; der Bool sagt, ob sie customfield anbieten.
     *
     * @return iterable<string, array{string, string, bool}>
     */
    public static function apis(): iterable
    {
        $billomat = self::client(new CapturedRequest(), '');

        foreach (new ReflectionObject($billomat)->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $api = $property->getValue($billomat);
            if (!\is_object($api)) {
                continue;
            }
            if (self::hasGetById($api)) {
                yield $property->name => [$property->name, '', method_exists($api, 'getCustomField')];
            }
            foreach (new ReflectionObject($api)->getProperties(ReflectionProperty::IS_PUBLIC) as $sub) {
                $subApi = $sub->getValue($api);
                if ($sub->hasHooks() && \is_object($subApi) && self::hasGetById($subApi)) {
                    yield $property->name.'->'.$sub->name => [$property->name, $sub->name, method_exists($subApi, 'getCustomField')];
                }
            }
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function customFieldApis(): iterable
    {
        foreach (self::apis() as $name => [$resource, $subResource, $supported]) {
            if ($supported) {
                yield $name => [$resource, $subResource];
            }
        }
    }

    /**
     * Billomat bietet den Endpoint genau für die Ressourcen an, deren Datensätze `customfield` haben
     * (in der Sandbox geprüft; Rollen, Freitexte und E-Mail-Vorlagen antworten mit 405).
     */
    #[Test]
    #[DataProvider('apis')]
    public function customFieldMethodsExistExactlyWhereTheModelHasTheField(string $resource, string $subResource, bool $supported): void
    {
        $mock = new MockHttpClient(static fn (string $method, string $url): MockResponse => new MockResponse((string) json_encode([
            self::envelopeKey($url) => ['id' => '7', 'unit_price' => '1', 'amount' => '1', 'customfield' => 'x'],
        ])));
        $billomat = new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));
        $api = $this->api($billomat, $resource, $subResource);

        $model = new ReflectionMethod($api, 'get')->invoke($api, 'incomingCategories' === $resource ? 'abc' : 7);

        self::assertIsObject($model);
        self::assertSame(property_exists($model, 'customField'), $supported);
        self::assertSame($supported, method_exists($api, 'setCustomField'));
    }

    #[Test]
    #[DataProvider('customFieldApis')]
    public function getCustomFieldReadsTheField(string $resource, string $subResource): void
    {
        $captured = new CapturedRequest();
        $billomat = self::client($captured, null, 'erp-4711');
        $api = $this->api($billomat, $resource, $subResource);

        $value = new ReflectionMethod($api, 'getCustomField')->invoke($api, 7);

        self::assertSame('erp-4711', $value);
        self::assertSame('GET', $captured->method);
        self::assertMatchesRegularExpression('#^/api/[a-z-]+/7/customfield$#', (string) $captured->path());
    }

    #[Test]
    #[DataProvider('customFieldApis')]
    public function getCustomFieldReturnsNullForAnEmptyField(string $resource, string $subResource): void
    {
        $api = $this->api(self::client(new CapturedRequest(), null, ''), $resource, $subResource);

        self::assertNull(new ReflectionMethod($api, 'getCustomField')->invoke($api, 7));
    }

    #[Test]
    #[DataProvider('customFieldApis')]
    public function setCustomFieldPutsTheValue(string $resource, string $subResource): void
    {
        $captured = new CapturedRequest();
        $api = $this->api(self::client($captured, null, 'erp-4711'), $resource, $subResource);

        new ReflectionMethod($api, 'setCustomField')->invoke($api, 7, 'erp-4711');

        self::assertSame('PUT', $captured->method);
        $path = (string) $captured->path();
        self::assertMatchesRegularExpression('#^/api/[a-z-]+/7/customfield$#', $path);
        self::assertSame([self::envelopeKey($path) => ['customfield' => 'erp-4711']], $captured->json());
    }

    #[Test]
    public function getCustomFieldThrowsForAMissingRecord(): void
    {
        $mock = new MockHttpClient(new MockResponse('{"errors":{"error":"item not found"}}', ['http_code' => 404]));
        $billomat = new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));

        $this->expectException(NotFoundException::class);

        (void) $billomat->clients->getCustomField(7);
    }

    #[Test]
    public function setCustomFieldWithAnEmptyStringClearsTheField(): void
    {
        $captured = new CapturedRequest();

        self::client($captured, null, '')->invoices->items->setCustomField(3, '');

        self::assertSame('/api/invoice-items/3/customfield', $captured->path());
        self::assertSame(['invoice-item' => ['customfield' => '']], $captured->json());
    }

    private static function hasGetById(object $api): bool
    {
        return method_exists($api, 'get') && 1 <= new ReflectionMethod($api, 'get')->getNumberOfRequiredParameters();
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

    /**
     * Envelope-Schlüssel aus dem Pfad: /credit-notes/7 → credit-note, /taxes/7 → tax, /article-properties/7 → article-property.
     */
    private static function envelopeKey(string $url): string
    {
        $slug = explode('/', (string) parse_url($url, PHP_URL_PATH))[2] ?? '';

        return (string) preg_replace(['/ies$/', '/(x)es$/', '/s$/'], ['y', '$1', ''], $slug);
    }

    /**
     * @param string|null $body        feste Antwort; null = customfield-Antwort mit `$customField` im passenden Envelope
     * @param string      $customField Wert in der customfield-Antwort
     */
    private static function client(CapturedRequest $captured, ?string $body, string $customField = ''): BillomatClient
    {
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured, $body, $customField): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse($body ?? (string) json_encode([self::envelopeKey($url) => ['id' => '7', 'customfield' => $customField]]));
        });

        return new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));
    }
}
