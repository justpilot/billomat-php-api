<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\InvoicePayment;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class InvoicePaymentsApiTest extends TestCase
{
    public function test_it_lists_payments_and_passes_filters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice-payments' => [
                    'invoice-payment' => [
                        [
                            'id' => 1,
                            'invoice_id' => 100,
                            'date' => '2025-01-01',
                            'amount' => 119.0,
                            'type' => 'BANK_TRANSFER',
                            'comment' => 'Testzahlung 1',
                        ],
                        [
                            'id' => 2,
                            'invoice_id' => 100,
                            'date' => '2025-01-02',
                            'amount' => 50.0,
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

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicePaymentsApi($http);

        $payments = $api->list([
            'invoice_id' => 100,
            'from' => '2025-01-01',
        ]);

        self::assertIsArray($payments);
        self::assertCount(2, $payments);
        self::assertContainsOnlyInstancesOf(InvoicePayment::class, $payments);

        $first = $payments[0];
        self::assertSame(1, $first->id);
        self::assertSame(100, $first->invoiceId);
        self::assertSame(119.0, $first->amount);
        self::assertInstanceOf(\DateTimeImmutable::class, $first->date);
        self::assertSame('2025-01-01', $first->date?->format('Y-m-d'));
        self::assertSame(InvoicePaymentType::BANK_TRANSFER, $first->type);
        self::assertSame('Testzahlung 1', $first->comment);

        // Request-Assertions
        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-payments?invoice_id=100&from=2025-01-01',
            $captured['url']
        );
    }

    public function test_it_gets_single_payment(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'invoice-payment' => [
                    'id' => 5,
                    'invoice_id' => 200,
                    'date' => '2025-02-10',
                    'amount' => 200.0,
                    'type' => 'PAYPAL',
                    'comment' => 'PayPal-Zahlung',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicePaymentsApi($http);

        $payment = $api->get(5);

        self::assertInstanceOf(InvoicePayment::class, $payment);
        self::assertSame(5, $payment->id);
        self::assertSame(200, $payment->invoiceId);
        self::assertSame(200.0, $payment->amount);
        self::assertSame('2025-02-10', $payment->date?->format('Y-m-d'));
        self::assertSame(InvoicePaymentType::PAYPAL, $payment->type);
        self::assertSame('PayPal-Zahlung', $payment->comment);
    }

    public function test_it_creates_payment_via_post(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Simulierte Response vom API
            $body = json_encode([
                'invoice-payment' => [
                    'id' => 10,
                    'invoice_id' => 300,
                    'date' => '2025-03-01',
                    'amount' => 119.0,
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

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicePaymentsApi($http);

        $opts = new InvoicePaymentCreateOptions(
            invoiceId: 300,
            amount: 119.0,
        );
        $opts->date = new \DateTimeImmutable('2025-03-01');
        $opts->type = InvoicePaymentType::BANK_TRANSFER;
        $opts->comment = 'Testzahlung';
        $opts->transactionPurpose = 'Rechnung RE-300';
        $opts->markInvoiceAsPaid = true;

        $payment = $api->create($opts);

        self::assertInstanceOf(InvoicePayment::class, $payment);
        self::assertSame(10, $payment->id);
        self::assertSame(300, $payment->invoiceId);
        self::assertSame(119.0, $payment->amount);
        self::assertSame('2025-03-01', $payment->date?->format('Y-m-d'));
        self::assertSame(InvoicePaymentType::BANK_TRANSFER, $payment->type);
        self::assertSame('Testzahlung', $payment->comment);

        // Request prüfen
        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-payments',
            $captured['url']
        );

        $options = $captured['options'] ?? [];
        $payload = $options['json'] ?? null;

        if ($payload === null && isset($options['body']) && is_string($options['body'])) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('invoice-payment', $payload);

        $inner = $payload['invoice-payment'];
        self::assertSame(300, $inner['invoice_id'] ?? null);
        self::assertSame(119.0, $inner['amount'] ?? null);
        self::assertSame('2025-03-01', $inner['date'] ?? null);
        self::assertSame('BANK_TRANSFER', $inner['type'] ?? null);
        self::assertSame('Testzahlung', $inner['comment'] ?? null);
        self::assertSame(1, $inner['mark_invoice_as_paid'] ?? null);
    }

    public function test_it_deletes_payment_via_delete(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicePaymentsApi($http);

        $result = $api->delete(42);

        self::assertTrue($result);
        self::assertSame('DELETE', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoice-payments/42',
            $captured['url']
        );
    }
}