<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Config;

use InvalidArgumentException;
use Justpilot\Billomat\Config\BillomatConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[Test]
    public function itUsesACustomBaseUri(): void
    {
        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            baseUri: 'http://localhost:8080/%s/api/',
        );

        self::assertSame('http://localhost:8080/mycompany/api/', $config->getBaseUri());
    }

    #[Test]
    #[DataProvider('invalidBillomatIds')]
    public function itRejectsInvalidBillomatIds(string $billomatId): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid Billomat ID');

        new BillomatConfig(billomatId: $billomatId, apiKey: 'secret-key');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidBillomatIds(): iterable
    {
        yield 'empty' => [''];
        yield 'slash' => ['evil.example/x'];
        yield 'at sign' => ['user@evil'];
        yield 'space' => ['my company'];
        yield 'leading hyphen' => ['-acme'];
        yield 'trailing hyphen' => ['acme-'];
        yield 'too long' => [str_repeat('a', 64)];
    }

    #[Test]
    public function itAcceptsHyphensAndDigits(): void
    {
        $config = new BillomatConfig(billomatId: 'acme-2', apiKey: 'secret-key');

        self::assertSame('https://acme-2.billomat.net/api/', $config->getBaseUri());
    }

    #[Test]
    #[DataProvider('invalidBaseUris')]
    public function itRejectsInvalidBaseUris(string $baseUri): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid base URI');

        new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key', baseUri: $baseUri);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidBaseUris(): iterable
    {
        yield 'relative' => ['/%s/api/'];
        yield 'no host' => ['mailto:%s'];
        yield 'invalid characters' => ['https://%s.billomat net/api/'];
    }
}
