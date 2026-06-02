<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\CreditNotePayment;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CreditNotePaymentsApi::class)]
#[CoversClass(CreditNotePaymentCreateOptions::class)]
#[CoversClass(CreditNotePayment::class)]
final class CreditNotePaymentsApiTest extends TestCase
{
    #[Test]
    public function itListsPaymentsAndPassesFilters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'credit-note-payments' => [
                    'credit-note-payment' => [
                        [
                            'id' => 1,
                            'credit_note_id' => 42,
                            'amount' => 100.0,
                            'date' => '2026-02-01',
                            'type' => 'BANK_TRANSFER',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotePaymentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $payments = $api->list(['credit_note_id' => 42]);

        self::assertCount(1, $payments);
        self::assertSame(100.0, $payments[0]->amount);
        self::assertSame(InvoicePaymentType::BANK_TRANSFER, $payments[0]->type);
        self::assertStringContainsString('credit_note_id=42', $captured['url']);
    }

    #[Test]
    public function itGetsSinglePayment(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'credit-note-payment' => [
                    'id' => 1,
                    'credit_note_id' => 42,
                    'amount' => 100.0,
                    'date' => '2026-02-01',
                    'type' => 'CASH',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotePaymentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $payment = $api->get(1);

        self::assertInstanceOf(CreditNotePayment::class, $payment);
        self::assertSame(InvoicePaymentType::CASH, $payment->type);
    }

    #[Test]
    public function itCreatesPaymentAndMarksAsPaid(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'credit-note-payment' => [
                    'id' => 99,
                    'credit_note_id' => 42,
                    'amount' => 100.0,
                    'type' => 'BANK_TRANSFER',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new CreditNotePaymentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new CreditNotePaymentCreateOptions(creditNoteId: 42, amount: 100.0);
        $opts->date = new DateTimeImmutable('2026-02-01');
        $opts->type = InvoicePaymentType::BANK_TRANSFER;
        $opts->markCreditNoteAsPaid = true;

        $created = $api->create($opts);

        self::assertSame(99, $created->id);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(42, $payload['credit-note-payment']['credit_note_id']);
        self::assertSame(100.0, $payload['credit-note-payment']['amount']);
        self::assertSame(1, $payload['credit-note-payment']['mark_credit_note_as_paid']);
        self::assertSame('BANK_TRANSFER', $payload['credit-note-payment']['type']);
    }

    #[Test]
    public function itDeletesPayment(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new CreditNotePaymentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(99));
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/credit-note-payments/99', $captured['url']);
    }
}
