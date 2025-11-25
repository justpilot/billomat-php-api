<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractApi;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException as BillomatHttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

/**
 * Kleine Test-API, um die geschützten Methoden von AbstractApi testen zu können.
 */
final class TestApi extends AbstractApi
{
    /** @param array<string, scalar|array|null> $query */
    public function publicGetJson(string $path, array $query = []): array
    {
        return $this->getJson($path, $query);
    }

    public function publicGetJsonOrNull(string $path): ?array
    {
        return $this->getJsonOrNull($path);
    }

    /** @param array<string,mixed> $body */
    public function publicPostJson(string $path, array $body): array
    {
        return $this->postJson($path, $body);
    }
}

final class AbstractApiExceptionTest extends TestCase
{
    public function test_getJson_maps_401_and_403_to_authentication_exception(): void
    {
        $statuses = [HttpStatus::HTTP_UNAUTHORIZED, HttpStatus::HTTP_FORBIDDEN];

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
                $this->fail(sprintf('Expected AuthenticationException for status %d', $status));
            } catch (AuthenticationException $e) {
                $this->assertSame($status, $e->getStatusCode());
            }

            // mock expectation zurücksetzen für nächste Schleifen-Iteration
            $this->addToAssertionCount(1);
        }
    }

    public function test_getJson_maps_400_and_422_to_validation_exception(): void
    {
        $statuses = [HttpStatus::HTTP_BAD_REQUEST, HttpStatus::HTTP_UNPROCESSABLE_ENTITY];

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
                $this->fail(sprintf('Expected ValidationException for status %d', $status));
            } catch (ValidationException $e) {
                $this->assertSame($status, $e->getStatusCode());
            }

            $this->addToAssertionCount(1);
        }
    }

    public function test_getJson_maps_other_4xx_5xx_to_generic_http_exception(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => HttpStatus::HTTP_INTERNAL_SERVER_ERROR,
        ]);

        $http = $this->createMock(BillomatHttpClientInterface::class);
        $http
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/clients', [])
            ->willReturn($mockResponse);

        $api = new TestApi($http);

        $this->expectException(BillomatHttpException::class);
        $this->expectExceptionCode(HttpStatus::HTTP_INTERNAL_SERVER_ERROR);

        $api->publicGetJson('/clients');
    }

    public function test_getJsonOrNull_returns_null_on_404(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => HttpStatus::HTTP_NOT_FOUND,
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

    public function test_getJsonOrNull_still_throws_for_non_404_errors(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => HttpStatus::HTTP_UNAUTHORIZED,
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

    public function test_postJson_uses_same_exception_mapping(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => HttpStatus::HTTP_BAD_REQUEST,
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
}