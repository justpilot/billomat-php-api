<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\PaymentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
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
#[UsesClass(InvoicesApi::class)]
#[CoversClass(InvoicePayment::class)]
#[UsesClass(InvoicePaymentCreateOptions::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(PaymentType::class)]
#[UsesClass(Money::class)]
final class InvoicePaymentsApiTest extends TestCase
{
    #[Test]
    public function itListsPaymentsAndPassesFilters(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->method = $method;
            $captured->url = $url;
            $captured->options = $options;

            $body = json_encode([
                'invoice-payments' => [
                    'invoice-payment' => [
                        [
                            'id' => 1,
                            'invoice_id' => 100,
                            'date' => '2025-01-01',
                            'amount' => '119',
                            'type' => 'BANK_TRANSFER',
                            'comment' => 'Testzahlung 1',
                        ],
                        [
                            'id' => 2,
                            'invoice_id' => 100,
                            'date' => '2025-01-02',
                            'amount' => '50',
                            'type' => 'CASH',
                            'comment' => 'Testzahlung 2',
                        ],
                    ],
                    '@page' => '1',
                    '@per_page' => '50',
                    '@total' => '2',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->payments;

        $payments = $api->list(filters: [
            'invoice_id' => 100,
            'from' => '2025-01-01',
        ]);

        self::assertIsArray($payments);
        self::assertCount(2, $payments);
        self::assertContainsOnlyInstancesOf(InvoicePayment::class, $payments);

        $first = $payments[0];
        self::assertSame(1, $first->id);
        self::assertSame(100, $first->invoiceId);
        self::assertSame('119', $first->amount->toString());
        self::assertInstanceOf(DateTimeImmutable::class, $first->date);
        self::assertSame('2025-01-01', $first->date->format('Y-m-d'));
        self::assertSame(PaymentType::BANK_TRANSFER, $first->type);
        self::assertSame('Testzahlung 1', $first->comment);

        // Request-Assertions
        self::assertSame('GET', $captured->method);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-payments?invoice_id=100&from=2025-01-01',
            $captured->url
        );
    }

    #[Test]
    public function itGetsSinglePayment(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'invoice-payment' => [
                    'id' => 5,
                    'invoice_id' => 200,
                    'date' => '2025-02-10',
                    'amount' => '200',
                    'type' => 'PAYPAL',
                    'comment' => 'PayPal-Zahlung',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->payments;

        $payment = $api->get(5);

        self::assertInstanceOf(InvoicePayment::class, $payment);
        self::assertSame(5, $payment->id);
        self::assertSame(200, $payment->invoiceId);
        self::assertSame('200', $payment->amount->toString());
        self::assertSame('2025-02-10', $payment->date?->format('Y-m-d'));
        self::assertSame(PaymentType::PAYPAL, $payment->type);
        self::assertSame('PayPal-Zahlung', $payment->comment);
    }

    #[Test]
    public function itCreatesPaymentViaPost(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->method = $method;
            $captured->url = $url;
            $captured->options = $options;

            // Simulierte Response vom API
            $body = json_encode([
                'invoice-payment' => [
                    'id' => 10,
                    'invoice_id' => 300,
                    'date' => '2025-03-01',
                    'amount' => '119',
                    'type' => 'BANK_TRANSFER',
                    'comment' => 'Testzahlung',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->payments;

        $opts = new InvoicePaymentCreateOptions(
            amount: Money::of('119'),
            date: new DateTimeImmutable('2025-03-01'),
            type: PaymentType::BANK_TRANSFER,
            comment: 'Testzahlung',
            transactionPurpose: 'Rechnung RE-300',
            markInvoiceAsPaid: true,
        );

        $payment = $api->create(300, $opts);

        self::assertSame(10, $payment->id);
        self::assertSame(300, $payment->invoiceId);
        self::assertSame('119', $payment->amount->toString());
        self::assertSame('2025-03-01', $payment->date?->format('Y-m-d'));
        self::assertSame(PaymentType::BANK_TRANSFER, $payment->type);
        self::assertSame('Testzahlung', $payment->comment);

        // Request prüfen
        self::assertSame('POST', $captured->method);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-payments',
            $captured->url
        );

        $payload = $captured->json();
        self::assertArrayHasKey('invoice-payment', $payload);

        $inner = $captured->jsonArray('invoice-payment');
        self::assertSame(300, $inner['invoice_id'] ?? null);
        self::assertSame('119', $inner['amount'] ?? null);
        self::assertSame('2025-03-01', $inner['date'] ?? null);
        self::assertSame('BANK_TRANSFER', $inner['type'] ?? null);
        self::assertSame('Testzahlung', $inner['comment'] ?? null);
        self::assertSame(1, $inner['mark_invoice_as_paid'] ?? null);
    }

    #[Test]
    public function itDeletesPaymentViaDelete(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->method = $method;
            $captured->url = $url;
            $captured->options = $options;

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient(new Psr18Client($mock), $config);
        $api = new InvoicesApi($http)->payments;

        $api->delete(42);
        self::assertSame('DELETE', $captured->method);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-payments/42',
            $captured->url
        );
    }

    #[Test]
    public function paymentModelHydratesCreatedUserIdAndTransactionPurpose(): void
    {
        $payment = InvoicePayment::fromArray([
            'id' => 77,
            'invoice_id' => 555,
            'user_id' => '9',
            'created' => '2026-02-03T10:15:30+01:00',
            'date' => '2026-02-03',
            'amount' => '119.00',
            'type' => 'BANK_TRANSFER',
            'comment' => 'Eingang vom 03.02.',
            'transaction_purpose' => 'Verwendungszweck: RE-2026-0001',
        ]);

        self::assertSame(77, $payment->id);
        self::assertSame(555, $payment->invoiceId);
        self::assertSame(9, $payment->userId);
        self::assertNotNull($payment->created);
        self::assertSame('2026-02-03', $payment->created->format('Y-m-d'));
        self::assertSame('Verwendungszweck: RE-2026-0001', $payment->transactionPurpose);
        self::assertSame(PaymentType::BANK_TRANSFER, $payment->type);

        $arr = $payment->toArray();
        self::assertSame(9, $arr['user_id']);
        self::assertSame('Verwendungszweck: RE-2026-0001', $arr['transaction_purpose']);
        self::assertArrayHasKey('created', $arr);
    }
}
