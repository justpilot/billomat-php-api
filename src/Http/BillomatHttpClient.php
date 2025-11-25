<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Http;

use Justpilot\Billomat\Config\BillomatConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class BillomatHttpClient
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
        ];

        if ($this->config->appId !== null && $this->config->appSecret !== null) {
            $headers['X-AppId'] = $this->config->appId;
            $headers['X-AppSecret'] = $this->config->appSecret;
        }

        if ($json !== null) {
            $headers['Content-Type'] = 'application/json';
        }

        return $this->client->request(
            $method,
            $this->config->getBaseUri() . ltrim($path, '/'),
            [
                'headers' => $headers,
                'query' => $query,
                'json' => $json,
                'timeout' => $this->config->timeout,
            ]
        );
    }
}