<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Config;

final readonly class BillomatConfig
{
    public function __construct(
        public string  $billomatId,
        public string  $apiKey,
        public ?string $appId = null,
        public ?string $appSecret = null,
        public string  $baseUri = 'https://%s.billomat.net/api/',
        public float   $timeout = 10.0,
    )
    {
    }

    public function getBaseUri(): string
    {
        return sprintf($this->baseUri, $this->billomatId);
    }
}