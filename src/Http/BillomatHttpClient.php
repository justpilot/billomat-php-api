<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Http;

use Justpilot\Billomat\Config\BillomatConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class BillomatHttpClient implements BillomatHttpClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private BillomatConfig      $config,
    )
    {
    }

    /**
     * @param array<string, scalar|array|null> $query
     * @param array<string, mixed>|null $json
     */
    public function request(
        string $method,
        string $path,
        array  $query = [],
        ?array $json = null
    ): ResponseInterface
    {
        $headers = [
            'X-BillomatApiKey' => $this->config->apiKey,
            'Accept' => 'application/json',
            'Accept-Language' => 'de-de'
        ];

        if ($this->config->appId !== null && $this->config->appSecret !== null) {
            $headers['X-AppId'] = $this->config->appId;
            $headers['X-AppSecret'] = $this->config->appSecret;
        }

        if ($json !== null) {
            $headers['Content-Type'] = 'application/json';
        }

        $url = $this->config->getBaseUri()
            . ltrim($path, '/')
            . $this->buildBillomatQuery($query);

        return $this->client->request(
            $method,
            $url,
            [
                'headers' => $headers,
                'json' => $json,
                'timeout' => $this->config->timeout,
            ]
        );
    }

    /**
     * Baut den Query-String für Billomat manuell.
     *
     * - null-Werte werden übersprungen
     * - Arrays werden als "key[]=v1&key[]=v2" serialisiert
     * - Werte werden RFC-konform encoded, ABER:
     *   - "+" bleibt ein Literal (z. B. "date+DESC"), weil Billomat "%2B" nicht versteht.
     *
     * @param array<string, scalar|array|null> $query
     */
    private function buildBillomatQuery(array $query): string
    {
        if ($query === []) {
            return '';
        }

        $parts = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            $encodedKey = rawurlencode((string)$key);

            if (is_array($value)) {
                foreach ($value as $element) {
                    if ($element === null) {
                        continue;
                    }

                    $parts[] = sprintf(
                        '%s[]=%s',
                        $encodedKey,
                        $this->encodeBillomatQueryValue($element)
                    );
                }

                continue;
            }

            $parts[] = sprintf(
                '%s=%s',
                $encodedKey,
                $this->encodeBillomatQueryValue($value)
            );
        }

        if ($parts === []) {
            return '';
        }

        return '?' . implode('&', $parts);
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
     *
     * @param scalar $value
     */
    private function encodeBillomatQueryValue(int|float|string|bool $value): string
    {
        $string = match (true) {
            is_bool($value) => $value ? '1' : '0',
            default => (string)$value,
        };

        $encoded = rawurlencode($string);

        // Billomat erwartet "+" literal (z. B. "date+DESC"),
        // versteht aber "%2B" nicht → wieder zurückwandeln.
        return str_replace('%2B', '+', $encoded);
    }
}