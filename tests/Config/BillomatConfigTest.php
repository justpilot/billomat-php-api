<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Config;

use Justpilot\Billomat\Config\BillomatConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BillomatConfig::class)]
final class BillomatConfigTest extends TestCase
{
    #[Test]
    public function itExposesBasicConfiguration(): void
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

    #[Test]
    public function itBuildsTheCorrectBaseUri(): void
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
