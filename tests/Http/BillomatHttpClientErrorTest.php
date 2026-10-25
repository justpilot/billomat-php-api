<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Http;

use InvalidArgumentException;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\RateLimitException;
use Justpilot\Billomat\Exception\TransportException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Fehler-Mapping, Retry und Rate-Limit-Behandlung im HTTP-Layer.
 */
#[CoversClass(BillomatHttpClient::class)]
#[CoversClass(RetryPolicy::class)]
#[CoversClass(HttpException::class)]
#[CoversClass(RateLimitException::class)]
#[CoversClass(TransportException::class)]
#[UsesClass(BillomatConfig::class)]
final class BillomatHttpClientErrorTest extends TestCase
{
    /** @var list<int> */
    private array $sleeps = [];

    /** @var list<array{method: string, url: string, body: string}> */
    private array $requests = [];

    /**
     * @param class-string<HttpException> $expected
     */
    #[Test]
    #[DataProvider('errorStatuses')]
    public function itMapsErrorStatusesToExceptions(int $status, string $expected): void
    {
        $client = $this->client([new MockResponse('', ['http_code' => $status])]);

        try {
            $client->request('GET', '/clients/1');
            self::fail('Expected an exception.');
        } catch (HttpException $e) {
            self::assertSame($e::class, $expected);
            self::assertSame($status, $e->getStatusCode());
            self::assertSame($status, $e->getCode());
            self::assertSame(\sprintf('Billomat responded with HTTP %d to GET /clients/1.', $status), $e->getMessage());
        }
    }

    /**
     * @return iterable<string, array{int, class-string<HttpException>}>
     */
    public static function errorStatuses(): iterable
    {
        yield '400' => [400, ValidationException::class];
        yield '401' => [401, AuthenticationException::class];
        yield '403' => [403, AuthenticationException::class];
        yield '404' => [404, NotFoundException::class];
        yield '409' => [409, HttpException::class];
        yield '422' => [422, ValidationException::class];
        yield '500' => [500, HttpException::class];
    }

    #[Test]
    public function itAppendsBillomatErrorTextFromJson(): void
    {
        $body = '{"errors":{"error":"Client name is missing"}}';
        $client = $this->client([new MockResponse($body, ['http_code' => 400])]);

        try {
            $client->request('POST', 'clients', [], ['client' => []]);
            self::fail('Expected an exception.');
        } catch (ValidationException $e) {
            self::assertSame('Billomat responded with HTTP 400 to POST /clients. Client name is missing', $e->getMessage());
            self::assertSame($body, $e->getResponseBody());
        }
    }

    #[Test]
    public function itAppendsEveryErrorFromAJsonList(): void
    {
        $client = $this->client([new MockResponse('{"errors":{"error":["First", 42, "Second"]}}', ['http_code' => 422])]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIsOrContains('Billomat responded with HTTP 422 to PUT /invoices/1. First Second');

        $client->request('PUT', '/invoices/1', [], []);
    }

    #[Test]
    public function itAppendsBillomatErrorTextFromXml(): void
    {
        $body = "<?xml version=\"1.0\"?>\n<errors>\n  <error> Resource not found </error>\n</errors>";
        $client = $this->client([new MockResponse($body, ['http_code' => 404])]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIsOrContains('Billomat responded with HTTP 404 to GET /clients/9. Resource not found');

        $client->request('GET', '/clients/9');
    }

    #[Test]
    public function itIgnoresJsonWithoutErrorsNode(): void
    {
        $client = $this->client([new MockResponse('{"message":"nope"}', ['http_code' => 500])]);

        $this->expectExceptionMessageIsOrContains('Billomat responded with HTTP 500 to GET /clients.');

        $client->request('GET', '/clients');
    }

    #[Test]
    public function itRetriesRateLimitedRequestsAfterRetryAfterSeconds(): void
    {
        $client = $this->client([
            new MockResponse('', ['http_code' => 429, 'response_headers' => ['Retry-After' => '2']]),
            new MockResponse('{"ok":true}', ['http_code' => 200]),
        ]);

        $response = $client->request('POST', '/clients', [], ['client' => ['name' => 'ACME']]);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([2000], $this->sleeps);
        self::assertCount(2, $this->requests);
        // Der Body wird für jeden Versuch neu aufgebaut.
        self::assertSame('{"client":{"name":"ACME"}}', $this->requests[1]['body']);
    }

    #[Test]
    public function itWaitsUntilTheRateLimitWindowResets(): void
    {
        $reset = time() + 3;
        $client = $this->client([
            new MockResponse('', ['http_code' => 429, 'response_headers' => ['X-Rate-Limit-Reset' => (string) $reset]]),
            new MockResponse('{}', ['http_code' => 200]),
        ]);

        $client->request('GET', '/clients');

        self::assertCount(1, $this->sleeps);
        self::assertContains($this->sleeps[0], [2000, 3000]);
    }

    #[Test]
    public function itReadsRetryAfterAsHttpDate(): void
    {
        $date = gmdate('D, d M Y H:i:s', time() + 5).' GMT';
        $client = $this->client([
            new MockResponse('', ['http_code' => 429, 'response_headers' => ['Retry-After' => $date]]),
            new MockResponse('{}', ['http_code' => 200]),
        ]);

        $client->request('GET', '/clients');

        self::assertContains($this->sleeps[0], [4000, 5000]);
    }

    #[Test]
    public function itFallsBackToBackoffWhenRateLimitHeadersAreMissing(): void
    {
        $client = $this->client([
            new MockResponse('', ['http_code' => 429, 'response_headers' => ['Retry-After' => 'soon']]),
            new MockResponse('{}', ['http_code' => 200]),
        ], new RetryPolicy(maxRetries: 1, baseDelayMs: 100));

        $client->request('GET', '/clients');

        self::assertCount(1, $this->sleeps);
        self::assertGreaterThanOrEqual(50, $this->sleeps[0]);
        self::assertLessThanOrEqual(100, $this->sleeps[0]);
    }

    #[Test]
    public function itThrowsRateLimitExceptionWhenTheWaitIsTooLong(): void
    {
        $client = $this->client([
            new MockResponse('<error>Maximum number of requests reached. Try again in 600 seconds.</error>', [
                'http_code' => 429,
                'response_headers' => ['Retry-After' => '600'],
            ]),
        ]);

        try {
            $client->request('GET', '/clients');
            self::fail('Expected a RateLimitException.');
        } catch (RateLimitException $e) {
            self::assertSame(429, $e->getStatusCode());
            self::assertSame(600, $e->getRetryAfter());
            self::assertStringContainsString('Try again in 600 seconds.', $e->getMessage());
        }

        self::assertSame([], $this->sleeps);
        self::assertCount(1, $this->requests);
    }

    #[Test]
    public function itThrowsRateLimitExceptionWhenRetriesAreExhausted(): void
    {
        $limited = static fn (): MockResponse => new MockResponse('', ['http_code' => 429, 'response_headers' => ['Retry-After' => '1']]);
        $client = $this->client([$limited(), $limited(), $limited()]);

        try {
            $client->request('GET', '/clients');
            self::fail('Expected a RateLimitException.');
        } catch (RateLimitException $e) {
            self::assertSame(1, $e->getRetryAfter());
        }

        self::assertSame([1000, 1000], $this->sleeps);
        self::assertCount(3, $this->requests);
    }

    #[Test]
    public function itRateLimitExceptionHasNoRetryAfterWithoutHeaders(): void
    {
        $client = $this->client([new MockResponse('', ['http_code' => 429])], RetryPolicy::none());

        try {
            $client->request('GET', '/clients');
            self::fail('Expected a RateLimitException.');
        } catch (RateLimitException $e) {
            self::assertNull($e->getRetryAfter());
        }
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function retryableStatuses(): iterable
    {
        yield '502' => [502];
        yield '503' => [503];
        yield '504' => [504];
    }

    #[Test]
    #[DataProvider('retryableStatuses')]
    public function itRetriesGetRequestsOnGatewayErrorsWithExponentialBackoff(int $status): void
    {
        $client = $this->client([
            new MockResponse('', ['http_code' => $status]),
            new MockResponse('', ['http_code' => $status]),
            new MockResponse('{}', ['http_code' => 200]),
        ], new RetryPolicy(maxRetries: 2, baseDelayMs: 100, maxDelayMs: 1000));

        $client->request('GET', '/clients');

        self::assertCount(2, $this->sleeps);
        self::assertGreaterThanOrEqual(50, $this->sleeps[0]);
        self::assertLessThanOrEqual(100, $this->sleeps[0]);
        self::assertGreaterThanOrEqual(100, $this->sleeps[1]);
        self::assertLessThanOrEqual(200, $this->sleeps[1]);
    }

    #[Test]
    public function itCapsTheBackoffAtMaxDelay(): void
    {
        $client = $this->client([
            new MockResponse('', ['http_code' => 503]),
            new MockResponse('', ['http_code' => 503]),
            new MockResponse('{}', ['http_code' => 200]),
        ], new RetryPolicy(maxRetries: 2, baseDelayMs: 1000, maxDelayMs: 300));

        $client->request('GET', '/clients');

        foreach ($this->sleeps as $sleep) {
            self::assertGreaterThanOrEqual(150, $sleep);
            self::assertLessThanOrEqual(300, $sleep);
        }
    }

    #[Test]
    #[DataProvider('retryableStatuses')]
    public function itPassesRetryAfterOfGatewayErrorsToTheException(int $status): void
    {
        $client = $this->client([new MockResponse('', [
            'http_code' => $status,
            'response_headers' => ['Retry-After' => '120', 'X-Rate-Limit-Reset' => (string) (time() + 900)],
        ])], RetryPolicy::none());

        try {
            $client->request('POST', '/invoices');
            self::fail('Expected an HttpException.');
        } catch (HttpException $e) {
            self::assertSame($status, $e->getStatusCode());
            self::assertSame(120, $e->getRetryAfter());
            self::assertTrue($e->isTransient());
            self::assertTrue($e->mayHaveBeenProcessed());
        }
    }

    #[Test]
    public function itIgnoresTheRateLimitResetOfGatewayErrors(): void
    {
        $client = $this->client([new MockResponse('', [
            'http_code' => 503,
            'response_headers' => ['X-Rate-Limit-Reset' => (string) (time() + 900)],
        ])], RetryPolicy::none());

        try {
            $client->request('GET', '/clients');
            self::fail('Expected an HttpException.');
        } catch (HttpException $e) {
            self::assertNull($e->getRetryAfter());
        }
    }

    #[Test]
    public function itSetsNoRetryAfterForOtherErrors(): void
    {
        $client = $this->client([new MockResponse('', [
            'http_code' => 500,
            'response_headers' => ['Retry-After' => '120'],
        ])]);

        try {
            $client->request('GET', '/clients');
            self::fail('Expected an HttpException.');
        } catch (HttpException $e) {
            self::assertNull($e->getRetryAfter());
        }
    }

    #[Test]
    public function itDoesNotRetryInternalServerErrors(): void
    {
        $client = $this->client([new MockResponse('', ['http_code' => 500])]);

        $this->expectException(HttpException::class);

        try {
            $client->request('GET', '/clients');
        } finally {
            self::assertCount(1, $this->requests);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function writeMethods(): iterable
    {
        yield 'POST' => ['POST'];
        yield 'PUT' => ['PUT'];
        yield 'DELETE' => ['DELETE'];
    }

    #[Test]
    #[DataProvider('writeMethods')]
    public function itDoesNotRetryWriteRequestsOnGatewayErrors(string $method): void
    {
        $client = $this->client([new MockResponse('', ['http_code' => 503])]);

        $this->expectException(HttpException::class);

        try {
            $client->request($method, '/invoices/1/complete');
        } finally {
            self::assertCount(1, $this->requests);
            self::assertSame([], $this->sleeps);
        }
    }

    #[Test]
    public function itRetriesGetRequestsOnNetworkErrors(): void
    {
        $client = $this->client([
            new MockResponse('', ['error' => 'Connection reset']),
            new MockResponse('{}', ['http_code' => 200]),
        ]);

        self::assertSame(200, $client->request('GET', '/clients')->getStatusCode());
        self::assertCount(1, $this->sleeps);
    }

    #[Test]
    public function itWrapsNetworkErrorsOfWriteRequestsWithoutRetry(): void
    {
        $client = $this->client([new MockResponse('', ['error' => 'Connection reset'])]);

        try {
            $client->request('POST', '/clients', [], []);
            self::fail('Expected a TransportException.');
        } catch (TransportException $e) {
            self::assertInstanceOf(NetworkExceptionInterface::class, $e->getPrevious());
            self::assertStringStartsWith('Request POST /clients to Billomat failed: ', $e->getMessage());
        }

        self::assertSame([], $this->sleeps);
    }

    #[Test]
    public function itWrapsNetworkErrorsOnceRetriesAreExhausted(): void
    {
        $client = $this->client([
            new MockResponse('', ['error' => 'Connection reset']),
            new MockResponse('', ['error' => 'Connection reset']),
        ], new RetryPolicy(maxRetries: 1));

        $this->expectException(TransportException::class);

        try {
            $client->request('GET', '/clients');
        } finally {
            self::assertCount(1, $this->sleeps);
        }
    }

    #[Test]
    public function itSendsJsonBodiesWithContentTypeAndZeroFractions(): void
    {
        $captured = [];
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = $options;

            return new MockResponse('{}');
        });
        $client = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret-key'));

        $client->request('POST', '/articles', [], ['article' => ['sales_price' => 25.0]]);

        self::assertSame('{"article":{"sales_price":25.0}}', $captured['body']);
        self::assertIsArray($captured['headers']);
        self::assertContains('Content-Type: application/json', $captured['headers']);
    }

    #[Test]
    public function itSendsNoBodyWithoutJson(): void
    {
        $captured = [];
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = $options;

            return new MockResponse('{}');
        });
        $client = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret-key'));

        $client->request('DELETE', '/clients/1');

        self::assertSame('', $captured['body']);
        self::assertIsArray($captured['headers']);
        self::assertNotContains('Content-Type: application/json', $captured['headers']);
    }

    #[Test]
    public function retryPolicyRejectsNegativeValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RetryPolicy(maxRetries: -1);
    }

    #[Test]
    public function retryPolicyNoneDisablesRetries(): void
    {
        self::assertSame(0, RetryPolicy::none()->maxRetries);
    }

    /**
     * @param list<MockResponse> $responses
     */
    private function client(array $responses, ?RetryPolicy $retry = null): BillomatHttpClient
    {
        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses): MockResponse {
            $body = $options['body'] ?? '';
            $this->requests[] = ['method' => $method, 'url' => $url, 'body' => \is_string($body) ? $body : ''];

            $response = array_shift($responses);
            self::assertInstanceOf(MockResponse::class, $response, 'Unexpected additional request.');

            return $response;
        });

        $config = new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key', retry: $retry ?? new RetryPolicy());

        return new BillomatHttpClient(new Psr18Client($mock), $config, sleep: function (int $milliseconds): void {
            $this->sleeps[] = $milliseconds;
        });
    }
}
