<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException as BillomatHttpException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Kleine Test-API, um die geschützten Methoden von AbstractApi testen zu können.
 */
final class TestApi extends AbstractApi
{
    /**
     * @param array<string, scalar|array|null> $query
     *
     * @return array<string,mixed>
     */
    public function publicGetJson(string $path, array $query = []): array
    {
        return $this->getJson($path, $query);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function publicGetJsonOrNull(string $path): ?array
    {
        return $this->getJsonOrNull($path);
    }

    /**
     * @param array<string,mixed> $body
     *
     * @return array<string,mixed>
     */
    public function publicPostJson(string $path, array $body): array
    {
        return $this->postJson($path, $body);
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    public function publicUnwrapEnvelope(array $payload, string $key): array
    {
        return $this->unwrapEnvelope($payload, $key, 'fetching client');
    }
}

#[CoversClass(AbstractApi::class)]
#[CoversClass(BillomatException::class)]
#[CoversClass(UnexpectedResponseException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpException::class)]
final class AbstractApiExceptionTest extends TestCase
{
    /**
     * @param class-string<BillomatHttpException> $expected
     */
    #[Test]
    #[DataProvider('errorStatuses')]
    public function getJsonPassesMappedHttpExceptionsThrough(int $status, string $expected): void
    {
        $api = $this->api(new MockResponse('', ['http_code' => $status]));

        try {
            $api->publicGetJson('/clients');
            self::fail(\sprintf('Expected %s for status %d', $expected, $status));
        } catch (BillomatHttpException $e) {
            self::assertInstanceOf($expected, $e);
            self::assertSame($status, $e->getStatusCode());
        }
    }

    /**
     * @return iterable<string, array{int, class-string<BillomatHttpException>}>
     */
    public static function errorStatuses(): iterable
    {
        yield '401' => [401, AuthenticationException::class];
        yield '403' => [403, AuthenticationException::class];
        yield '400' => [400, ValidationException::class];
        yield '422' => [422, ValidationException::class];
        yield '500' => [500, BillomatHttpException::class];
    }

    #[Test]
    public function getJsonOrNullReturnsNullOn404(): void
    {
        $api = $this->api(new MockResponse('', ['http_code' => 404]));

        self::assertNull($api->publicGetJsonOrNull('/clients/9999'));
    }

    #[Test]
    public function getJsonOrNullStillThrowsForNon404Errors(): void
    {
        $api = $this->api(new MockResponse('', ['http_code' => 401]));

        $this->expectException(AuthenticationException::class);

        $api->publicGetJsonOrNull('/clients/1');
    }

    #[Test]
    public function postJsonUsesSameExceptionMapping(): void
    {
        $api = $this->api(new MockResponse('', ['http_code' => 400]));

        $this->expectException(ValidationException::class);

        $api->publicPostJson('/clients', ['foo' => 'bar']);
    }

    #[Test]
    public function invalidJsonBodyThrowsUnexpectedResponseException(): void
    {
        $api = $this->api(new MockResponse('<html>Wartungsarbeiten</html>', ['http_code' => 200]));

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('the body is not valid JSON');

        $api->publicGetJson('/clients');
    }

    #[Test]
    public function scalarJsonBodyThrowsUnexpectedResponseException(): void
    {
        $api = $this->api(new MockResponse('"ok"', ['http_code' => 200]));

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('expected a JSON object');

        $api->publicGetJson('/clients');
    }

    #[Test]
    public function unwrapEnvelopeThrowsUnexpectedResponseExceptionOnMissingWrapper(): void
    {
        $api = new TestApi(self::createStub(BillomatHttpClientInterface::class));

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('Unexpected response from Billomat when fetching client.');

        $api->publicUnwrapEnvelope(['invoice' => ['id' => 1]], 'client');
    }

    #[Test]
    public function unwrapEnvelopeReturnsInnerNode(): void
    {
        $api = new TestApi(self::createStub(BillomatHttpClientInterface::class));

        self::assertSame(['id' => 1], $api->publicUnwrapEnvelope(['client' => ['id' => 1]], 'client'));
    }

    private function api(MockResponse $response): TestApi
    {
        $config = new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key', retry: RetryPolicy::none());

        return new TestApi(new BillomatHttpClient(new Psr18Client(new MockHttpClient($response)), $config));
    }
}
