<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Http;

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Http\Discovery\Psr17FactoryDiscovery;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\RateLimitException;
use Justpilot\Billomat\Exception\TransportException;
use Justpilot\Billomat\Exception\ValidationException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

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
     * @param RequestFactoryInterface|null $requestFactory Default: per php-http/discovery gefunden
     * @param StreamFactoryInterface|null  $streamFactory  Default: per php-http/discovery gefunden
     * @param (Closure(int): void)|null    $sleep          wartet die übergebenen Millisekunden (für Tests austauschbar)
     */
    public function __construct(
        private ClientInterface $client,
        private BillomatConfig $config,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?Closure $sleep = null,
    ) {
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->sleep = $sleep ?? static function (int $milliseconds): void {
            usleep($milliseconds * 1000);
        };
    }

    /**
     * @param array<string, scalar|array|null> $query
     * @param array<string, mixed>|null        $json
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $json = null
    ): ResponseInterface {
        $retry = $this->config->retry;
        $queryString = $this->buildBillomatQuery($query);

        for ($attempt = 0;; ++$attempt) {
            $mayRetry = $attempt < $retry->maxRetries;

            try {
                $response = $this->client->sendRequest($this->buildRequest($method, $path, $queryString, $json));
            } catch (NetworkExceptionInterface $e) {
                if ($mayRetry && 'GET' === $method) {
                    ($this->sleep)($this->backoff($attempt));

                    continue;
                }

                throw $this->transportException($method, $path, $e);
            } catch (ClientExceptionInterface $e) {
                throw $this->transportException($method, $path, $e);
            }

            $status = $response->getStatusCode();

            if ($status < 400) {
                return $response;
            }

            if (429 === $status) {
                $retryAfter = $this->retryAfterSeconds($response);
                $delay = null !== $retryAfter ? $retryAfter * 1000 : $this->backoff($attempt);

                if ($mayRetry && $delay <= $retry->maxDelayMs) {
                    ($this->sleep)($delay);

                    continue;
                }

                throw new RateLimitException($this->errorMessage($response, $method, $path), $status, (string) $response->getBody(), retryAfter: $retryAfter);
            }

            if ($mayRetry && 'GET' === $method && \in_array($status, [502, 503, 504], true)) {
                ($this->sleep)($this->backoff($attempt));

                continue;
            }

            throw $this->httpException($response, $method, $path);
        }
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
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Language', 'de-de');

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
     * Wartezeit in Sekunden, die Billomat bei 429 mitteilt.
     *
     * Billomat setzt `X-Rate-Limit-Reset` (Epoch-Sekunden des nächsten Fensters);
     * ein Standard-`Retry-After` (Sekunden oder HTTP-Datum) hat Vorrang.
     */
    private function retryAfterSeconds(ResponseInterface $response): ?int
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

        if (ctype_digit($reset)) {
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
     * @param array<string, scalar|array|null> $query
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
