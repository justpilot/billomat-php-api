<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingsApi;
use Justpilot\Billomat\Api\PaymentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\IncomingPayment;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(PaymentsApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[UsesClass(IncomingsApi::class)]
#[CoversClass(IncomingPaymentCreateOptions::class)]
#[CoversClass(IncomingPayment::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(PaymentType::class)]
#[UsesClass(Money::class)]
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
                        ['id' => 1, 'incoming_id' => 42, 'amount' => '100', 'type' => 'BANK_TRANSFER'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->payments;

        $payments = $api->list(filters: ['incoming_id' => 42]);
        self::assertCount(1, $payments);
        self::assertSame(PaymentType::BANK_TRANSFER, $payments[0]->type);
    }

    #[Test]
    public function itCreatesPaymentMarkingAsPaid(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'options' => $options];

            $body = json_encode([
                'incoming-payment' => ['id' => 99, 'incoming_id' => 42, 'amount' => '100'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->payments;

        $opts = new IncomingPaymentCreateOptions(incomingId: 42, amount: Money::of('100'), markIncomingAsPaid: true);

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

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->payments;

        $api->delete(99);
        self::assertSame('DELETE', $captured['method']);
    }
}
