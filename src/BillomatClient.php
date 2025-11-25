<?php

declare(strict_types=1);

namespace Justpilot\Billomat;

use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BillomatClient
{
    private BillomatHttpClient $http;

    public readonly ClientsApi $clients;

    public function __construct(
        BillomatConfig       $config,
        ?HttpClientInterface $httpClient = null,
    )
    {
        $httpClient ??= HttpClient::create();

        $this->http = new BillomatHttpClient($httpClient, $config);

        // APIs
        $this->clients = new ClientsApi($this->http);
        // später: $this->invoices = new InvoicesApi($this->http); etc.
    }

    public static function create(
        string               $billomatId,
        string               $apiKey,
        ?string              $appId = null,
        ?string              $appSecret = null,
        float                $timeout = 10.0,
        ?HttpClientInterface $httpClient = null,
    ): self
    {
        $config = new BillomatConfig(
            billomatId: $billomatId,
            apiKey: $apiKey,
            appId: $appId,
            appSecret: $appSecret,
            timeout: $timeout,
        );

        return new self($config, $httpClient);
    }

    public function getHttpClient(): BillomatHttpClient
    {
        return $this->http;
    }
}