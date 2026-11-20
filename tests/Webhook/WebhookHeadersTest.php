<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Webhook;

use Justpilot\Billomat\Exception\InvalidWebhookException;
use Justpilot\Billomat\Webhook\WebhookEvent;
use Justpilot\Billomat\Webhook\WebhookHeaders;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(WebhookHeaders::class)]
#[UsesClass(WebhookEvent::class)]
final class WebhookHeadersTest extends TestCase
{
    /**
     * @param array<array-key, mixed> $headers
     */
    #[Test]
    #[DataProvider('headerShapes')]
    public function itReadsTheHeaders(array $headers): void
    {
        $parsed = WebhookHeaders::fromArray($headers);

        self::assertSame(WebhookEvent::INVOICE_PAYMENT_CREATE, $parsed->event);
        self::assertSame(1792857986, $parsed->requestId);
        self::assertSame(17, $parsed->webhookId);
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function headerShapes(): iterable
    {
        yield 'Header-Namen' => [[
            'X-Billomat-Webhook-Event' => 'invoice_payment.create',
            'X-Billomat-Webhook-Request-Id' => '1792857986',
            'X-Billomat-Webhook-Id' => '17',
        ]];
        yield 'klein geschrieben, als Listen (Symfony)' => [[
            'x-billomat-webhook-event' => ['invoice_payment.create'],
            'x-billomat-webhook-request-id' => ['1792857986'],
            'x-billomat-webhook-id' => ['17', '18'],
        ]];
        yield '$_SERVER' => [[
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_BILLOMAT_WEBHOOK_EVENT' => 'invoice_payment.create',
            'HTTP_X_BILLOMAT_WEBHOOK_REQUEST_ID' => '1792857986',
            'HTTP_X_BILLOMAT_WEBHOOK_ID' => '17',
            'argv' => [],
            'REQUEST_TIME' => 1792857986,
            'X-Debug' => new stdClass(),
            'X-Forwarded-For' => null,
        ]];
        yield 'Ganzzahlen und Leerraum' => [[
            'X-Billomat-Webhook-Event' => ' invoice_payment.create ',
            'X-Billomat-Webhook-Request-Id' => 1792857986,
            'X-Billomat-Webhook-Id' => 17,
        ]];
    }

    #[Test]
    public function itReadsAPsrServerRequest(): void
    {
        $request = new ServerRequest('POST', '/billomat/webhook', [
            'X-Billomat-Webhook-Event' => 'invoice.status',
            'X-Billomat-Webhook-Request-Id' => '1792857895',
        ]);

        $parsed = WebhookHeaders::fromServerRequest($request);

        self::assertSame(WebhookEvent::INVOICE_STATUS, $parsed->event);
        self::assertSame(1792857895, $parsed->requestId);
        self::assertNull($parsed->webhookId);
    }

    #[Test]
    public function aNonNumericWebhookIdIsIgnored(): void
    {
        $parsed = WebhookHeaders::fromArray([
            'X-Billomat-Webhook-Event' => 'invoice.status',
            'X-Billomat-Webhook-Request-Id' => '5',
            'X-Billomat-Webhook-Id' => 'abc',
        ]);

        self::assertNull($parsed->webhookId);
    }

    /**
     * @param array<array-key, mixed> $headers
     */
    #[Test]
    #[DataProvider('invalidHeaders')]
    public function itRejectsInvalidHeaders(array $headers, string $message): void
    {
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessageIsOrContains($message);

        WebhookHeaders::fromArray($headers);
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>, string}>
     */
    public static function invalidHeaders(): iterable
    {
        yield 'ohne Event' => [['X-Billomat-Webhook-Request-Id' => '5'], 'Missing header X-Billomat-Webhook-Event.'];
        yield 'leeres Event' => [['X-Billomat-Webhook-Event' => '', 'X-Billomat-Webhook-Request-Id' => '5'], 'Missing header X-Billomat-Webhook-Event.'];
        yield 'leere Liste' => [['X-Billomat-Webhook-Event' => [], 'X-Billomat-Webhook-Request-Id' => '5'], 'Missing header X-Billomat-Webhook-Event.'];
        yield 'unbekanntes Event' => [['X-Billomat-Webhook-Event' => 'invoice.paid', 'X-Billomat-Webhook-Request-Id' => '5'], 'Unknown Billomat webhook event "invoice.paid".'];
        yield 'ohne Request-ID' => [['X-Billomat-Webhook-Event' => 'invoice.status'], 'Missing or invalid header X-Billomat-Webhook-Request-Id: "".'];
        yield 'Request-ID nicht numerisch' => [['X-Billomat-Webhook-Event' => 'invoice.status', 'X-Billomat-Webhook-Request-Id' => 'a1'], 'Missing or invalid header X-Billomat-Webhook-Request-Id: "a1".'];
        yield 'negative Request-ID' => [['X-Billomat-Webhook-Event' => 'invoice.status', 'X-Billomat-Webhook-Request-Id' => '-1'], 'Missing or invalid header X-Billomat-Webhook-Request-Id: "-1".'];
    }
}
