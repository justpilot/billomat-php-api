<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Api\ReminderUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Enum\ReminderStatus;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\ReminderPdf;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(RemindersApi::class)]
#[CoversClass(ReminderCreateOptions::class)]
#[CoversClass(ReminderUpdateOptions::class)]
#[CoversClass(EmailOptions::class)]
#[CoversClass(Reminder::class)]
#[CoversClass(ReminderPdf::class)]
#[CoversClass(ReminderStatus::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ListCaster::class)]
#[UsesClass(ScalarCaster::class)]
final class RemindersApiTest extends TestCase
{
    #[Test]
    public function itListsReminders(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'reminders' => [
                    'reminder' => [
                        ['id' => 1, 'client_id' => 123, 'invoice_id' => 42, 'status' => 'OPEN'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $reminders = $api->list();

        self::assertCount(1, $reminders);
        self::assertSame(ReminderStatus::OPEN, $reminders[0]->status);
        self::assertSame(42, $reminders[0]->invoiceId);
    }

    #[Test]
    public function itGetsSingleReminder(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'reminder' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'invoice_id' => 42,
                    'status' => 'OPEN',
                    'reminder_number' => 'M-2026-0001',
                    'subject' => '1. Mahnung',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $reminder = $api->get(1234);

        self::assertInstanceOf(Reminder::class, $reminder);
        self::assertSame('1. Mahnung', $reminder->subject);
    }

    #[Test]
    public function itCreatesReminderViaPost(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'reminder' => [
                    'id' => 777,
                    'client_id' => 123,
                    'invoice_id' => 42,
                    'status' => 'DRAFT',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ReminderCreateOptions(
            invoiceId: 42,
            date: new DateTimeImmutable('2026-03-01'),
            dueDays: 7,
            subject: '1. Mahnung',
        );

        $reminder = $api->create($opts);

        self::assertSame(777, $reminder->id);

        self::assertSame(42, $captured->jsonAt('reminder', 'invoice_id'));
        self::assertSame('1. Mahnung', $captured->jsonAt('reminder', 'subject'));
        self::assertSame(7, $captured->jsonAt('reminder', 'due_days'));
    }

    #[Test]
    public function itUpdatesReminder(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            $body = json_encode([
                'reminder' => ['id' => 777, 'client_id' => 123, 'subject' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ReminderUpdateOptions(subject: 'Geändert');

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->subject);
        self::assertSame('PUT', $captured->method);
    }

    #[Test]
    public function itPerformsStatusActions(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured[] = CapturedRequest::of($method, $url);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $api->complete(1);
        $api->cancel(2);
        $api->delete(3);

        self::assertSame('https://mycompany.billomat.net/api/reminders/1/complete', $captured[0]->url);
        self::assertSame('https://mycompany.billomat.net/api/reminders/2/cancel', $captured[1]->url);
        self::assertSame('DELETE', $captured[2]->method);
    }

    #[Test]
    public function itEmailsReminder(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new EmailOptions(to: ['kunde@example.com']);

        $api->email(42, $opts);

        self::assertSame(['kunde@example.com'], $captured->jsonAt('email', 'recipients', 'to'));
    }

    #[Test]
    public function itFetchesReminderPdf(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'pdf' => [
                    'id' => 4882,
                    'reminder_id' => 240,
                    'filename' => 'mahnung.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 70137,
                    'base64file' => base64_encode('%PDF-FAKE%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $pdf = $api->pdf(240);

        self::assertInstanceOf(ReminderPdf::class, $pdf);
        self::assertSame(240, $pdf->reminderId);
        self::assertStringStartsWith('%PDF-FAKE%', $pdf->getBinary());
    }

    #[Test]
    public function itFetchesRawPdf(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            return new MockResponse('%PDF-1.4 RAW%', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->pdf(777, PdfType::PRINT, rawPdf: true);

        self::assertIsString($result);
        self::assertStringContainsString('format=pdf', $captured->url);
        self::assertStringContainsString('type=print', $captured->url);
    }

    #[Test]
    public function itUploadsSignaturePdf(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $api->uploadSignature(555, base64_encode('PDF'));
        self::assertSame('PUT', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/reminders/555/upload-signature', $captured->url);
    }

    #[Test]
    public function completeMapsHttpErrorToValidationException(): void
    {
        // Regression: vor Bug-Fix verschluckte `complete()` 4xx/5xx und gab still `false` zurück.
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['errors' => ['error' => 'Reminder cannot be completed.']], JSON_THROW_ON_ERROR),
            ['http_code' => 422],
        ));

        $api = new RemindersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->complete(88);
    }
}
