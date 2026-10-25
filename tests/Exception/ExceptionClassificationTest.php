<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Exception;

use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\RateLimitException;
use Justpilot\Billomat\Exception\TransportException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

#[CoversClass(BillomatException::class)]
#[CoversClass(HttpException::class)]
#[CoversClass(TransportException::class)]
final class ExceptionClassificationTest extends TestCase
{
    #[Test]
    #[DataProvider('exceptions')]
    public function itClassifiesTheError(BillomatException $e, bool $transient, bool $processed): void
    {
        self::assertSame($transient, $e->isTransient());
        self::assertSame($processed, $e->mayHaveBeenProcessed());
    }

    /**
     * @return iterable<string, array{BillomatException, bool, bool}>
     */
    public static function exceptions(): iterable
    {
        yield '400' => [new ValidationException('', 400), false, false];
        yield '401' => [new AuthenticationException('', 401), false, false];
        yield '404' => [new NotFoundException('', 404), false, false];
        yield '409' => [new HttpException('', 409), false, false];
        yield '429' => [new RateLimitException('', 429), true, false];
        yield '499' => [new HttpException('', 499), false, false];
        yield '500' => [new HttpException('', 500), false, true];
        yield '501' => [new HttpException('', 501), false, true];
        yield '502' => [new HttpException('', 502), true, true];
        yield '503' => [new HttpException('', 503), true, true];
        yield '504' => [new HttpException('', 504), true, true];
        yield '505' => [new HttpException('', 505), false, true];
        yield 'Netzwerkfehler' => [new TransportException('', 0, self::networkException()), true, true];
        yield 'ohne previous' => [new TransportException(''), true, true];
        yield 'ungültiger Request' => [new TransportException('', 0, self::requestException()), false, false];
        yield 'unerwartete Antwort' => [new UnexpectedResponseException(''), false, true];
    }

    #[Test]
    public function retryAfterIsOptional(): void
    {
        self::assertNull(new HttpException('', 503)->getRetryAfter());
        self::assertSame(30, new HttpException('', 503, retryAfter: 30)->getRetryAfter());
        self::assertSame(60, new RateLimitException('', 429, retryAfter: 60)->getRetryAfter());
    }

    private static function networkException(): NetworkExceptionInterface
    {
        return new class('Connection reset') extends RuntimeException implements NetworkExceptionInterface {
            public function getRequest(): RequestInterface
            {
                throw new LogicException('nicht benötigt');
            }
        };
    }

    private static function requestException(): RequestExceptionInterface
    {
        return new class('Invalid URI') extends RuntimeException implements RequestExceptionInterface {
            public function getRequest(): RequestInterface
            {
                throw new LogicException('nicht benötigt');
            }
        };
    }
}
