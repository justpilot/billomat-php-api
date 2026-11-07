<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Http;

use Justpilot\Billomat\Config\BillomatConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class BillomatHttpClient implements BillomatHttpClientInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private BillomatConfig $config,
    ) {
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
        $headers = [
            'X-BillomatApiKey' => $this->config->apiKey,
            'Accept' => 'application/json',
            'Accept-Language' => 'de-de',
        ];

        if (null !== $this->config->appId && null !== $this->config->appSecret) {
            $headers['X-AppId'] = $this->config->appId;
            $headers['X-AppSecret'] = $this->config->appSecret;
        }

        if (null !== $json) {
            $headers['Content-Type'] = 'application/json';
        }

        $url = $this->config->getBaseUri()
            .ltrim($path, '/')
            .$this->buildBillomatQuery($query);

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
     * - Werte werden RFC-konform encoded ("+" in Filterwerten wird "%2B", Leerzeichen "%20")
     * - `order_by`: das "+" zwischen Feld und Richtung ("date+DESC") wird ein Leerzeichen
     *   ("date%20DESC"). Billomat versteht "date%2BDESC" nicht; ein unkodiertes "+"
     *   würde dagegen in jedem anderen Wert als Leerzeichen gelesen ("a+b@x.de").
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

            if ('order_by' === $key && \is_string($value)) {
                $value = str_replace('+', ' ', $value);
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
     * - rawurlencode für sauberes Encoding ("+" → "%2B", Leerzeichen → "%20")
     */
    private function encodeBillomatQueryValue(int|float|string|bool $value): string
    {
        $string = match (true) {
            \is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };

        return rawurlencode($string);
    }
}
