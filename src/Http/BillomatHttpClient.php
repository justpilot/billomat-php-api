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

        $queryBuild = [];
        foreach ($query as $key => $value) {
            $queryBuild[] = $key . '=' . $value;
        }

        return $this->client->request(
            $method,
            $this->config->getBaseUri() . ltrim($path, '/') . '?' . implode('&', $queryBuild),
            [
                'headers' => $headers,
                //'query' => $query,
                'json' => $json,
                'timeout' => $this->config->timeout,
            ]
        );
    }
}