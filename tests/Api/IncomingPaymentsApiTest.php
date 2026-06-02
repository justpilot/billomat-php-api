<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingPaymentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\IncomingPayment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(IncomingPaymentsApi::class)]
#[CoversClass(IncomingPaymentCreateOptions::class)]
#[CoversClass(IncomingPayment::class)]
final class IncomingPaymentsApiTest extends TestCase
{
    #[Test]
    public function itListsPayments(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'incoming-payments' => [
                    'incoming-payment' => [
                        ['id' => 1, 'incoming_id' => 42, 'amount' => 100.0, 'type' => 'BANK_TRANSFER'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingPaymentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $payments = $api->list(['incoming_id' => 42]);
        self::assertCount(1, $payments);
        self::assertSame(InvoicePaymentType::BANK_TRANSFER, $payments[0]->type);
    }

    #[Test]
    public function itCreatesPaymentMarkingAsPaid(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'options' => $options];

            $body = json_encode([
                'incoming-payment' => ['id' => 99, 'incoming_id' => 42, 'amount' => 100.0],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new IncomingPaymentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new IncomingPaymentCreateOptions(incomingId: 42, amount: 100.0);
        $opts->markIncomingAsPaid = true;

        $created = $api->create($opts);
        self::assertSame(99, $created->id);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(1, $payload['incoming-payment']['mark_incoming_as_paid']);
    }

    #[Test]
    public function itDeletesPayment(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new IncomingPaymentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(99));
        self::assertSame('DELETE', $captured['method']);
    }
}
