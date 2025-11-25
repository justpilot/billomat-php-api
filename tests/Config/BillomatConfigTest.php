<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Config;

use Justpilot\Billomat\Config\BillomatConfig;
use PHPUnit\Framework\TestCase;

final class BillomatConfigTest extends TestCase
{
    public function test_it_exposes_basic_configuration(): void
    {
        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            appId: 'app-id',
            appSecret: 'app-secret',
            timeout: 5.0,
        );

        self::assertSame('mycompany', $config->billomatId);
        self::assertSame('secret-key', $config->apiKey);
        self::assertSame('app-id', $config->appId);
        self::assertSame('app-secret', $config->appSecret);
        self::assertSame(5.0, $config->timeout);
    }

    public function test_it_builds_the_correct_base_uri(): void
    {
        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        self::assertSame(
            'https://mycompany.billomat.net/api/',
            $config->getBaseUri()
        );
    }
}