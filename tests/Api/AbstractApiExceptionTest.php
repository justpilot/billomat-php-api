<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractApi;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException as BillomatHttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
#[CoversClass(AuthenticationException::class)]
#[CoversClass(NotFoundException::class)]
#[CoversClass(ValidationException::class)]
#[CoversClass(BillomatHttpException::class)]
#[CoversClass(UnexpectedResponseException::class)]
final class AbstractApiExceptionTest extends TestCase
{
    #[Test]
    public function getJsonMaps401And403ToAuthenticationException(): void
    {
        $statuses = [401, 403];

        foreach ($statuses as $status) {
            $mockResponse = new MockResponse('', [
                'http_code' => $status,
            ]);

            $http = $this->createMock(BillomatHttpClientInterface::class);
            $http
                ->expects($this->once())
                ->method('request')
                ->with('GET', '/clients', [])
                ->willReturn($mockResponse);

            $api = new TestApi($http);

            try {
                $api->publicGetJson('/clients');
                $this->fail(\sprintf('Expected AuthenticationException for status %d', $status));
            } catch (AuthenticationException $e) {
                $this->assertSame($status, $e->getStatusCode());
            }

            // mock expectation zurücksetzen für nächste Schleifen-Iteration
            $this->addToAssertionCount(1);
        }
    }

    #[Test]
    public function getJsonMaps400And422ToValidationException(): void
    {
        $statuses = [400, 422];

        foreach ($statuses as $status) {
            $mockResponse = new MockResponse('', [
                'http_code' => $status,
            ]);

            $http = $this->createMock(BillomatHttpClientInterface::class);
            $http
                ->expects($this->once())
                ->method('request')
                ->with('GET', '/clients', [])
                ->willReturn($mockResponse);

            $api = new TestApi($http);

            try {
                $api->publicGetJson('/clients');
                $this->fail(\sprintf('Expected ValidationException for status %d', $status));
            } catch (ValidationException $e) {
                $this->assertSame($status, $e->getStatusCode());
            }

            $this->addToAssertionCount(1);
        }
    }

    #[Test]
    public function getJsonMapsOther4xx5xxToGenericHttpException(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 500,
        ]);

        $http = $this->createMock(BillomatHttpClientInterface::class);
        $http
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/clients', [])
            ->willReturn($mockResponse);

        $api = new TestApi($http);

        $this->expectException(BillomatHttpException::class);
        $this->expectExceptionCode(500);

        $api->publicGetJson('/clients');
    }

    #[Test]
    public function getJsonOrNullReturnsNullOn404(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 404,
        ]);

        $http = $this->createMock(BillomatHttpClientInterface::class);
        $http
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/clients/9999')
            ->willReturn($mockResponse);

        $api = new TestApi($http);

        $result = $api->publicGetJsonOrNull('/clients/9999');

        $this->assertNull($result);
    }

    #[Test]
    public function getJsonOrNullStillThrowsForNon404Errors(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 401,
        ]);

        $http = $this->createMock(BillomatHttpClientInterface::class);
        $http
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/clients/1')
            ->willReturn($mockResponse);

        $api = new TestApi($http);

        $this->expectException(AuthenticationException::class);

        $api->publicGetJsonOrNull('/clients/1');
    }

    #[Test]
    public function postJsonUsesSameExceptionMapping(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 400,
        ]);

        $http = $this->createMock(BillomatHttpClientInterface::class);
        $http
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/clients', [], ['foo' => 'bar'])
            ->willReturn($mockResponse);

        $api = new TestApi($http);

        $this->expectException(ValidationException::class);

        $api->publicPostJson('/clients', ['foo' => 'bar']);
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
}
