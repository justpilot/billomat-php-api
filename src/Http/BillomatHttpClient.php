<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Http;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Http\Discovery\Psr17FactoryDiscovery;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Event\RequestFailed;
use Justpilot\Billomat\Event\RequestStarted;
use Justpilot\Billomat\Event\ResponseReceived;
use Justpilot\Billomat\Event\RetryScheduled;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\BillomatException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\RateLimitException;
use Justpilot\Billomat\Exception\TransportException;
use Justpilot\Billomat\Exception\ValidationException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_THROW_ON_ERROR;

/**
 * Transport-Schicht des SDK auf Basis von PSR-18/PSR-17.
 *
 * Setzt Auth-Header, baut die URL samt Billomat-Query-String, übersetzt
 * 4xx/5xx in SDK-Exceptions und wiederholt Requests gemäß {@see BillomatConfig::$retry}.
 */
final readonly class BillomatHttpClient implements BillomatHttpClientInterface
{
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    /** @var Closure(int): void */
    private Closure $sleep;

    /**
     * @param RequestFactoryInterface|null  $requestFactory Default: per php-http/discovery gefunden
     * @param StreamFactoryInterface|null   $streamFactory  Default: per php-http/discovery gefunden
     * @param (Closure(int): void)|null     $sleep          wartet die übergebenen Millisekunden (für Tests austauschbar)
     * @param LoggerInterface|null          $logger         PSR-3: Antworten (debug), Retries (info), Fehler (notice/error)
     * @param EventDispatcherInterface|null $events         PSR-14: {@see RequestStarted}, {@see ResponseReceived}, {@see RetryScheduled}, {@see RequestFailed}
     */
    public function __construct(
        private ClientInterface $client,
        private BillomatConfig $config,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?Closure $sleep = null,
        private ?LoggerInterface $logger = null,
        private ?EventDispatcherInterface $events = null,
    ) {
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->sleep = $sleep ?? static function (int $milliseconds): void {
            usleep($milliseconds * 1000);
        };
    }

    /**
     * @param array<string, scalar|array<scalar|null>|null> $query
     * @param array<string, mixed>|null                     $json
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $json = null
    ): ResponseInterface {
        $retry = $this->config->retry;
        $queryString = $this->buildBillomatQuery($query);

        for ($attempt = 1;; ++$attempt) {
            $mayRetry = $attempt <= $retry->maxRetries;
            $request = $this->buildRequest($method, $path, $queryString, $json);
            $this->events?->dispatch(new RequestStarted($request, $attempt));
            $start = hrtime(true);

            try {
                $response = $this->client->sendRequest($request);
            } catch (NetworkExceptionInterface $e) {
                if ($mayRetry && 'GET' === $method) {
                    $this->scheduleRetry($request, $attempt, $this->backoff($attempt - 1), 'network error: '.$e->getMessage(), error: $e);

                    continue;
                }

                throw $this->fail($request, $attempt, $this->transportException($method, $path, $e));
            } catch (ClientExceptionInterface $e) {
                throw $this->fail($request, $attempt, $this->transportException($method, $path, $e));
            }

            $durationMs = (hrtime(true) - $start) / 1e6;
            $this->events?->dispatch(new ResponseReceived($request, $response, $attempt, $durationMs));
            $status = $response->getStatusCode();

            if ($status < 400) {
                $this->logger?->debug('Billomat {method} {path}: HTTP {status} in {duration_ms} ms', [
                    ...$this->logContext($request, $attempt),
                    'status' => $status,
                    'duration_ms' => $durationMs,
                ]);

                return $response;
            }

            if (429 === $status) {
                $retryAfter = $this->retryAfterSeconds($response);
                $delay = null !== $retryAfter ? $retryAfter * 1000 : $this->backoff($attempt - 1);

                if ($mayRetry && $delay <= $retry->maxDelayMs) {
                    $this->scheduleRetry($request, $attempt, $delay, 'HTTP 429', response: $response);

                    continue;
                }

                throw $this->fail($request, $attempt, new RateLimitException($this->errorMessage($response, $method, $path), $status, (string) $response->getBody(), retryAfter: $retryAfter));
            }

            if ($mayRetry && 'GET' === $method && \in_array($status, [502, 503, 504], true)) {
                $this->scheduleRetry($request, $attempt, $this->backoff($attempt - 1), 'HTTP '.$status, response: $response);

                continue;
            }

            throw $this->fail($request, $attempt, $this->httpException($response, $method, $path));
        }
    }

    /**
     * Meldet den Retry (Event, Log) und wartet die Verzögerung ab.
     *
     * Ein Retry ist erwartetes Verhalten und geht als `info` ins Log; erst ein endgültiger Fehler
     * ist eine Warnung wert (siehe {@see fail()}).
     */
    private function scheduleRetry(RequestInterface $request, int $attempt, int $delayMs, string $reason, ?ResponseInterface $response = null, ?ClientExceptionInterface $error = null): void
    {
        $this->events?->dispatch(new RetryScheduled($request, $attempt, $delayMs, $response, $error));
        $this->logger?->info('Billomat {method} {path}: {reason}, retrying in {delay_ms} ms (attempt {attempt})', [
            ...$this->logContext($request, $attempt),
            'reason' => $reason,
            'delay_ms' => $delayMs,
        ]);

        ($this->sleep)($delayMs);
    }

    /**
     * Meldet den endgültigen Fehler (Event, Log) und gibt die Exception zum Werfen zurück.
     *
     * Client-Fehler (4xx) sind oft erwartet (z. B. 404 aus `get()`) und gehen als `notice` ins Log,
     * alles andere als `error`.
     */
    private function fail(RequestInterface $request, int $attempt, BillomatException $exception): BillomatException
    {
        $this->events?->dispatch(new RequestFailed($request, $exception, $attempt));

        $status = $exception instanceof HttpException ? $exception->getStatusCode() : null;
        $this->logger?->log(
            null !== $status && $status < 500 ? LogLevel::NOTICE : LogLevel::ERROR,
            'Billomat {method} {path} failed: {message}',
            [...$this->logContext($request, $attempt), 'status' => $status, 'message' => $exception->getMessage(), 'exception' => $exception],
        );

        return $exception;
    }

    /**
     * Log-Kontext ohne Header, Query und Body: Darin können API-Key und personenbezogene Daten stehen.
     *
     * @return array{method: string, path: string, attempt: int}
     */
    private function logContext(RequestInterface $request, int $attempt): array
    {
        return [
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'attempt' => $attempt,
        ];
    }

    /**
     * Baut den Request für jeden Versuch neu, damit der Body-Stream nicht schon gelesen ist.
     *
     * @param array<string, mixed>|null $json
     */
    private function buildRequest(string $method, string $path, string $queryString, ?array $json): RequestInterface
    {
        $url = $this->config->getBaseUri().ltrim($path, '/').$queryString;

        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('X-BillomatApiKey', $this->config->apiKey)
            ->withHeader('Accept', 'application/json');

        if (null !== $this->config->language) {
            $request = $request->withHeader('Accept-Language', $this->config->language);
        }

        if (null !== $this->config->appId && null !== $this->config->appSecret) {
            $request = $request
                ->withHeader('X-AppId', $this->config->appId)
                ->withHeader('X-AppSecret', $this->config->appSecret);
        }

        if (null !== $json) {
            return $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(json_encode($json, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR)));
        }

        return $request;
    }

    /**
     * Exponentielles Backoff mit Jitter in Millisekunden, gedeckelt durch `maxDelayMs`.
     */
    private function backoff(int $attempt): int
    {
        $retry = $this->config->retry;
        $delay = min($retry->maxDelayMs, $retry->baseDelayMs * 2 ** $attempt);

        return random_int(intdiv($delay, 2), $delay);
    }

    /**
     * Wartezeit in Sekunden, die Billomat mitteilt.
     *
     * Ein Standard-`Retry-After` (Sekunden oder HTTP-Datum) hat Vorrang. Bei 429 setzt
     * Billomat `X-Rate-Limit-Reset` (Epoch-Sekunden des nächsten Fensters); den Header
     * schickt Billomat aber bei jeder Response mit, daher zählt er nur bei `$rateLimited`.
     */
    private function retryAfterSeconds(ResponseInterface $response, bool $rateLimited = true): ?int
    {
        $retryAfter = $response->getHeaderLine('Retry-After');

        if ('' !== $retryAfter) {
            if (ctype_digit($retryAfter)) {
                return (int) $retryAfter;
            }

            $date = DateTimeImmutable::createFromFormat('D, d M Y H:i:s \\G\\M\\T', $retryAfter, new DateTimeZone('UTC'));

            if (false !== $date) {
                return max(0, $date->getTimestamp() - time());
            }
        }

        $reset = $response->getHeaderLine('X-Rate-Limit-Reset');

        if ($rateLimited && ctype_digit($reset)) {
            return max(0, (int) $reset - time());
        }

        return null;
    }

    /**
     * Übersetzt eine Fehler-Response in die passende SDK-Exception.
     */
    private function httpException(ResponseInterface $response, string $method, string $path): HttpException
    {
        $status = $response->getStatusCode();
        $message = $this->errorMessage($response, $method, $path);
        $body = (string) $response->getBody();

        return match ($status) {
            401, 403 => new AuthenticationException($message, $status, $body),
            404 => new NotFoundException($message, $status, $body),
            400, 422 => new ValidationException($message, $status, $body),
            502, 503, 504 => new HttpException($message, $status, $body, retryAfter: $this->retryAfterSeconds($response, rateLimited: false)),
            default => new HttpException($message, $status, $body),
        };
    }

    /**
     * Baut die Exception-Message samt Billomat-Fehlertext, falls der Body einen enthält.
     *
     * Billomat liefert Fehler als `{"errors": {"error": "…"}}` (bzw. mit einer Liste)
     * oder als XML `<error>…</error>`.
     */
    private function errorMessage(ResponseInterface $response, string $method, string $path): string
    {
        $message = \sprintf('Billomat responded with HTTP %d to %s /%s.', $response->getStatusCode(), $method, ltrim($path, '/'));

        $body = (string) $response->getBody();
        $errors = [];

        $decoded = json_decode($body, true);

        if (\is_array($decoded) && \is_array($decoded['errors'] ?? null)) {
            $error = $decoded['errors']['error'] ?? null;
            $errors = array_values(array_filter(\is_array($error) ? $error : [$error], \is_string(...)));
        } elseif (preg_match_all('#<error>(.*?)</error>#s', $body, $matches) > 0) {
            $errors = $matches[1];
        }

        return [] === $errors ? $message : $message.' '.implode(' ', array_map(trim(...), $errors));
    }

    private function transportException(string $method, string $path, ClientExceptionInterface $e): TransportException
    {
        return new TransportException(
            \sprintf('Request %s /%s to Billomat failed: %s', $method, ltrim($path, '/'), $e->getMessage()),
            0,
            $e,
        );
    }

    /**
     * Baut den Query-String für Billomat manuell.
     *
     * - null-Werte werden übersprungen
     * - Arrays werden als "key[]=v1&key[]=v2" serialisiert (die PSR-7-URI kodiert die Klammern als "%5B%5D")
     * - Werte werden RFC-konform encoded, ABER:
     *   - "+" bleibt ein Literal (z. B. "date+DESC"), weil Billomat "%2B" nicht versteht.
     *
     * @param array<string, scalar|array<scalar|null>|null> $query
     */
    private function buildBillomatQuery(array $query): string
    {
        if ([] === $query) {
            return '';
        }

        $parts = [];

        foreach ($query as $key => $value) {
            if (null === $value) {
                continue;
            }

            $encodedKey = rawurlencode((string) $key);

            if (\is_array($value)) {
                foreach ($value as $element) {
                    if (null === $element) {
                        continue;
                    }

                    $parts[] = \sprintf(
                        '%s[]=%s',
                        $encodedKey,
                        $this->encodeBillomatQueryValue($element)
                    );
                }

                continue;
            }

            $parts[] = \sprintf(
                '%s=%s',
                $encodedKey,
                $this->encodeBillomatQueryValue($value)
            );
        }

        if ([] === $parts) {
            return '';
        }

        return '?'.implode('&', $parts);
    }

    /**
     * Encodiert einen einzelnen Query-Wert.
     *
     * - bool → "1"/"0"
     * - alles andere → String
     * - rawurlencode für sauberes Encoding
     * - "%2B" wird wieder in "+" zurückverwandelt,
     *   weil Billomat z. B. "date+DESC" erwartet
     *   und "date%2BDESC" als unbekanntes Feld interpretiert.
     */
    private function encodeBillomatQueryValue(int|float|string|bool $value): string
    {
        $string = match (true) {
            \is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };

        $encoded = rawurlencode($string);

        // Billomat erwartet "+" literal (z. B. "date+DESC"),
        // versteht aber "%2B" nicht → wieder zurückwandeln.
        return str_replace('%2B', '+', $encoded);
    }
}
