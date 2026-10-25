<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Http;

use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Event\RequestFailed;
use Justpilot\Billomat\Event\RequestStarted;
use Justpilot\Billomat\Event\ResponseReceived;
use Justpilot\Billomat\Event\RetryScheduled;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\RateLimitException;
use Justpilot\Billomat\Exception\TransportException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Tests\Support\RecordingEventDispatcher;
use Justpilot\Billomat\Tests\Support\RecordingLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * PSR-3-Logging und PSR-14-Events von {@see BillomatHttpClient}.
 */
#[CoversClass(BillomatHttpClient::class)]
#[CoversClass(RequestStarted::class)]
#[CoversClass(ResponseReceived::class)]
#[CoversClass(RetryScheduled::class)]
#[CoversClass(RequestFailed::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatException::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(NotFoundException::class)]
#[UsesClass(RateLimitException::class)]
#[UsesClass(TransportException::class)]
final class BillomatHttpClientObservabilityTest extends TestCase
{
    private RecordingEventDispatcher $dispatcher;

    private RecordingLogger $logger;

    /** @var list<int> */
    private array $sleeps = [];

    protected function setUp(): void
    {
        $this->dispatcher = new RecordingEventDispatcher();
        $this->logger = new RecordingLogger();
    }

    #[Test]
    public function successfulRequestDispatchesStartAndResponseAndLogsDebug(): void
    {
        $response = $this->client([new MockResponse('{}')])->request('GET', '/clients', ['name' => 'Max']);

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(2, $this->dispatcher->events);
        self::assertInstanceOf(RequestStarted::class, $this->dispatcher->events[0]);
        self::assertSame(1, $this->dispatcher->events[0]->attempt);
        self::assertSame('GET', $this->dispatcher->events[0]->request->getMethod());
        self::assertInstanceOf(ResponseReceived::class, $this->dispatcher->events[1]);
        self::assertSame(1, $this->dispatcher->events[1]->attempt);
        self::assertSame($this->dispatcher->events[0]->request, $this->dispatcher->events[1]->request);
        self::assertSame(200, $this->dispatcher->events[1]->response->getStatusCode());
        self::assertGreaterThanOrEqual(0.0, $this->dispatcher->events[1]->durationMs);

        self::assertCount(1, $this->logger->logs);
        self::assertSame('debug', $this->logger->logs[0]['level']);
        self::assertSame('Billomat {method} {path}: HTTP {status} in {duration_ms} ms', $this->logger->logs[0]['message']);
        self::assertSame(['method', 'path', 'attempt', 'status', 'duration_ms'], array_keys($this->logger->logs[0]['context']));
        self::assertSame('GET', $this->logger->logs[0]['context']['method']);
        self::assertSame('/api/clients', $this->logger->logs[0]['context']['path']);
        self::assertSame(1, $this->logger->logs[0]['context']['attempt']);
        self::assertSame(200, $this->logger->logs[0]['context']['status']);
        self::assertIsFloat($this->logger->logs[0]['context']['duration_ms']);
    }

    #[Test]
    public function logContextHoldsNoApiKeyQueryOrBody(): void
    {
        $this->client([new MockResponse('{}'), new MockResponse('', ['http_code' => 422])], retry: RetryPolicy::none())
            ->request('GET', '/clients', ['email' => 'max@example.com']);

        try {
            $this->client([new MockResponse('{"errors":{"error":"Name fehlt"}}', ['http_code' => 422])], retry: RetryPolicy::none())
                ->request('POST', '/clients', [], ['client' => ['name' => 'Geheim GmbH']]);
        } catch (HttpException) {
        }

        foreach ($this->logger->logs as $log) {
            $context = $log['context'];
            unset($context['exception']);
            $encoded = (string) json_encode($context);
            self::assertStringNotContainsString('secret-key', $encoded);
            self::assertStringNotContainsString('max@example.com', $encoded);
            self::assertStringNotContainsString('Geheim GmbH', $encoded);
        }
    }

    #[Test]
    public function retryAfterServerErrorIsDispatchedAndLoggedAsWarning(): void
    {
        $this->client([new MockResponse('', ['http_code' => 503]), new MockResponse('{}')])->request('GET', '/clients');

        self::assertSame(
            [RequestStarted::class, ResponseReceived::class, RetryScheduled::class, RequestStarted::class, ResponseReceived::class],
            $this->dispatcher->classes(),
        );
        $retry = $this->dispatcher->events[2];
        self::assertInstanceOf(RetryScheduled::class, $retry);
        self::assertSame(1, $retry->attempt);
        self::assertSame($this->sleeps[0], $retry->delayMs);
        self::assertSame(503, $retry->response?->getStatusCode());
        self::assertNull($retry->error);
        self::assertInstanceOf(RequestStarted::class, $this->dispatcher->events[3]);
        self::assertSame(2, $this->dispatcher->events[3]->attempt);

        self::assertSame(['warning', 'debug'], $this->logger->levels());
        self::assertSame('Billomat {method} {path}: {reason}, retrying in {delay_ms} ms (attempt {attempt})', $this->logger->logs[0]['message']);
        self::assertSame('HTTP 503', $this->logger->logs[0]['context']['reason']);
        self::assertSame($this->sleeps[0], $this->logger->logs[0]['context']['delay_ms']);
        self::assertSame(1, $this->logger->logs[0]['context']['attempt']);
        self::assertSame(2, $this->logger->logs[1]['context']['attempt']);
    }

    #[Test]
    public function retryAfterRateLimitCarriesTheResponse(): void
    {
        $this->client([new MockResponse('', ['http_code' => 429, 'response_headers' => ['Retry-After' => '1']]), new MockResponse('{}')])
            ->request('POST', '/clients', [], ['client' => []]);

        $retry = $this->dispatcher->events[2];
        self::assertInstanceOf(RetryScheduled::class, $retry);
        self::assertSame(1000, $retry->delayMs);
        self::assertSame(429, $retry->response?->getStatusCode());
        self::assertSame('HTTP 429', $this->logger->logs[0]['context']['reason']);
    }

    #[Test]
    public function retryAfterNetworkErrorCarriesTheError(): void
    {
        $this->client([new MockResponse('', ['error' => 'Connection reset']), new MockResponse('{}')])->request('GET', '/clients');

        self::assertSame(
            [RequestStarted::class, RetryScheduled::class, RequestStarted::class, ResponseReceived::class],
            $this->dispatcher->classes(),
        );
        $retry = $this->dispatcher->events[1];
        self::assertInstanceOf(RetryScheduled::class, $retry);
        self::assertNull($retry->response);
        self::assertInstanceOf(NetworkExceptionInterface::class, $retry->error);
        self::assertIsString($this->logger->logs[0]['context']['reason']);
        self::assertStringStartsWith('network error: ', $this->logger->logs[0]['context']['reason']);
        self::assertStringContainsString('Connection reset', $this->logger->logs[0]['context']['reason']);
    }

    #[Test]
    public function clientErrorIsDispatchedAndLoggedAsNotice(): void
    {
        try {
            $this->client([new MockResponse('', ['http_code' => 404])])->request('GET', '/clients/7');
            self::fail('NotFoundException erwartet.');
        } catch (NotFoundException $e) {
            $failed = $this->dispatcher->events[2];
            self::assertInstanceOf(RequestFailed::class, $failed);
            self::assertSame($e, $failed->exception);
            self::assertSame(1, $failed->attempt);
            self::assertSame('/api/clients/7', $failed->request->getUri()->getPath());
        }

        self::assertSame(['notice'], $this->logger->levels());
        self::assertSame('Billomat {method} {path} failed: {message}', $this->logger->logs[0]['message']);
        self::assertSame(404, $this->logger->logs[0]['context']['status']);
        self::assertSame($e->getMessage(), $this->logger->logs[0]['context']['message']);
        self::assertSame($e, $this->logger->logs[0]['context']['exception']);
    }

    #[Test]
    public function exhaustedRateLimitIsLoggedAsNotice(): void
    {
        try {
            $this->client([new MockResponse('', ['http_code' => 429])], retry: RetryPolicy::none())->request('GET', '/clients');
            self::fail('RateLimitException erwartet.');
        } catch (RateLimitException $e) {
            self::assertInstanceOf(RequestFailed::class, $this->dispatcher->events[2]);
            self::assertSame($e, $this->dispatcher->events[2]->exception);
        }

        self::assertSame(['notice'], $this->logger->levels());
        self::assertSame(429, $this->logger->logs[0]['context']['status']);
    }

    #[Test]
    public function serverErrorIsLoggedAsError(): void
    {
        try {
            $this->client([new MockResponse('', ['http_code' => 500])])->request('POST', '/clients', [], ['client' => []]);
            self::fail('HttpException erwartet.');
        } catch (HttpException) {
        }

        self::assertSame(['error'], $this->logger->levels());
        self::assertSame(500, $this->logger->logs[0]['context']['status']);
    }

    #[Test]
    public function finalNetworkErrorIsDispatchedAndLoggedAsError(): void
    {
        try {
            $this->client([new MockResponse('', ['error' => 'Connection reset'])])->request('POST', '/clients', [], ['client' => []]);
            self::fail('TransportException erwartet.');
        } catch (TransportException $e) {
            self::assertSame([RequestStarted::class, RequestFailed::class], $this->dispatcher->classes());
            self::assertInstanceOf(RequestFailed::class, $this->dispatcher->events[1]);
            self::assertSame($e, $this->dispatcher->events[1]->exception);
        }

        self::assertSame(['error'], $this->logger->levels());
        self::assertNull($this->logger->logs[0]['context']['status']);
    }

    #[Test]
    public function otherClientExceptionsAreDispatchedAndLoggedAsError(): void
    {
        $exception = new class('Ungültige URL') extends RuntimeException implements ClientExceptionInterface {
        };
        $client = new BillomatHttpClient(
            new readonly class($exception) implements ClientInterface {
                public function __construct(private ClientExceptionInterface $exception)
                {
                }

                public function sendRequest(RequestInterface $request): ResponseInterface
                {
                    throw $this->exception;
                }
            },
            new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key'),
            logger: $this->logger,
            events: $this->dispatcher,
        );

        try {
            $client->request('GET', '/clients');
            self::fail('TransportException erwartet.');
        } catch (TransportException $e) {
            self::assertSame($exception, $e->getPrevious());
            self::assertInstanceOf(RequestFailed::class, $this->dispatcher->events[1]);
            self::assertSame($e, $this->dispatcher->events[1]->exception);
        }

        self::assertSame(['error'], $this->logger->levels());
    }

    #[Test]
    public function worksWithoutLoggerAndDispatcher(): void
    {
        $client = new BillomatHttpClient(
            new Psr18Client(new MockHttpClient([new MockResponse('', ['http_code' => 503]), new MockResponse('', ['http_code' => 404])])),
            new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key'),
            sleep: static function (): void {
            },
        );

        $this->expectException(NotFoundException::class);

        $client->request('GET', '/clients/7');
    }

    /**
     * @param list<MockResponse> $responses
     */
    private function client(array $responses, ?RetryPolicy $retry = null): BillomatHttpClient
    {
        return new BillomatHttpClient(
            new Psr18Client(new MockHttpClient($responses)),
            new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key', retry: $retry ?? new RetryPolicy()),
            sleep: function (int $milliseconds): void {
                $this->sleeps[] = $milliseconds;
            },
            logger: $this->logger,
            events: $this->dispatcher,
        );
    }
}
