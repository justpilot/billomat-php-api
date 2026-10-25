<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Config;

use InvalidArgumentException;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BillomatConfig::class)]
#[CoversClass(RetryPolicy::class)]
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
    public function itRetriesTwiceByDefault(): void
    {
        $retry = new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key')->retry;

        self::assertSame(2, $retry->maxRetries);
        self::assertSame(500, $retry->baseDelayMs);
        self::assertSame(10_000, $retry->maxDelayMs);
    }

    #[Test]
    public function retryPolicyAcceptsZero(): void
    {
        $retry = new RetryPolicy(maxRetries: 0, baseDelayMs: 0, maxDelayMs: 0);

        self::assertSame(0, $retry->maxDelayMs);
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function negativeRetrySettings(): iterable
    {
        yield 'maxRetries' => [-1, 0, 0];
        yield 'baseDelayMs' => [0, -1, 0];
        yield 'maxDelayMs' => [0, 0, -1];
    }

    #[Test]
    #[DataProvider('negativeRetrySettings')]
    public function retryPolicyRejectsNegativeSettings(int $maxRetries, int $baseDelayMs, int $maxDelayMs): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Retry settings must not be negative.');

        new RetryPolicy($maxRetries, $baseDelayMs, $maxDelayMs);
    }

    #[Test]
    public function itSendsGermanByDefault(): void
    {
        self::assertSame('de-de', new BillomatConfig(billomatId: 'mycompany', apiKey: 'k')->language);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validLanguages(): iterable
    {
        yield 'Sprache mit Region' => ['en-gb'];
        yield 'Liste mit Gewichtung' => ['de-DE, en;q=0.8'];
        yield 'Platzhalter' => ['*'];
    }

    #[Test]
    #[DataProvider('validLanguages')]
    public function itAcceptsAcceptLanguageValues(string $language): void
    {
        self::assertSame($language, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k', language: $language)->language);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidLanguages(): iterable
    {
        yield 'leer' => [''];
        yield 'Zeilenumbruch' => ["de-de\r\nX-Injected: 1"];
        yield 'beginnt mit Leerzeichen' => [' de'];
        yield 'Sonderzeichen' => ['de/de'];
    }

    #[Test]
    #[DataProvider('invalidLanguages')]
    public function itRejectsInvalidLanguages(string $language): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid language "'.$language.'"');

        new BillomatConfig(billomatId: 'mycompany', apiKey: 'k', language: $language);
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
