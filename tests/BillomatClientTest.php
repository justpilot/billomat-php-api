<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests;

use Justpilot\Billomat\Api\AbstractApi;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Event\RequestStarted;
use Justpilot\Billomat\Event\ResponseReceived;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Tests\Support\RecordingEventDispatcher;
use Justpilot\Billomat\Tests\Support\RecordingLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use ReflectionProperty;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(BillomatClient::class)]
#[UsesClass(AbstractApi::class)]
#[UsesNamespace('Justpilot\\Billomat\\Api')]
#[UsesClass(ClientsApi::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(RequestStarted::class)]
#[UsesClass(ResponseReceived::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Client::class)]
#[UsesClass(ListCaster::class)]
final class BillomatClientTest extends TestCase
{
    #[Test]
    public function itWiresClientsApiAndUsesConfig(): void
    {
        $responses = [
            new MockResponse(json_encode([
                'clients' => [
                    'client' => [
                        ['id' => 1, 'name' => 'Client A'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ];

        $mockHttp = new MockHttpClient($responses);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $client = new BillomatClient($config, new Psr18Client($mockHttp));

        $result = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(Client::class, $result);

        $first = $result[0];
        self::assertSame('Client A', $first->name);
        self::assertSame(1, $first->id);
    }

    #[Test]
    public function staticCreateHelperBuildsConfigAndWiresApis(): void
    {
        $mockHttp = new MockHttpClient([
            new MockResponse(json_encode([
                'clients' => [
                    'client' => [],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $client = BillomatClient::create(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            httpClient: new Psr18Client($mockHttp),
        );

        // Smoke-Test: Clients-API funktioniert und liefert ein Array von Client-Objekten
        $clients = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        // zusätzliche Sicherstellung: weitere APIs sind verdrahtet
    }

    #[Test]
    public function apisAreCreatedOnFirstAccessAndReused(): void
    {
        $client = BillomatClient::create(billomatId: 'mycompany', apiKey: 'k', httpClient: new Psr18Client(new MockHttpClient()));
        $properties = new ReflectionObject($client)->getProperties(ReflectionProperty::IS_PUBLIC);

        self::assertCount(30, $properties);
        foreach ($properties as $property) {
            // Vor dem ersten Zugriff existiert die API noch nicht.
            self::assertFalse($property->isInitialized($client), $property->name);

            $api = $property->getValue($client);
            self::assertInstanceOf(AbstractApi::class, $api, $property->name);
            self::assertSame($api, $property->getValue($client), $property->name);
            self::assertTrue($property->isPrivateSet(), $property->name);
        }
    }

    #[Test]
    public function createPassesLoggerAndEventDispatcher(): void
    {
        $dispatcher = new RecordingEventDispatcher();
        $logger = new RecordingLogger();

        $client = BillomatClient::create(
            billomatId: 'mycompany',
            apiKey: 'k',
            httpClient: new Psr18Client(new MockHttpClient(new MockResponse('{}'))),
            logger: $logger,
            events: $dispatcher,
        );
        $client->getHttpClient()->request('GET', '/clients');

        self::assertSame([RequestStarted::class, ResponseReceived::class], $dispatcher->classes());
        self::assertSame(['debug'], $logger->levels());
    }

    #[Test]
    public function createPassesTheLanguage(): void
    {
        $headers = [];
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$headers): MockResponse {
            $headers = $options['headers'] ?? [];

            return new MockResponse('{}');
        });
        $client = BillomatClient::create(billomatId: 'mycompany', apiKey: 'k', httpClient: new Psr18Client($mock), language: 'en-gb');

        $client->getHttpClient()->request('GET', '/clients');

        self::assertIsArray($headers);
        self::assertContains('Accept-Language: en-gb', $headers);
    }
}
