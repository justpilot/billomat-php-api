<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\ReminderEmailOptions;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Api\ReminderUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Enum\ReminderStatus;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Model\ReminderPdf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(RemindersApi::class)]
#[CoversClass(ReminderCreateOptions::class)]
#[CoversClass(ReminderUpdateOptions::class)]
#[CoversClass(ReminderEmailOptions::class)]
#[CoversClass(Reminder::class)]
#[CoversClass(ReminderPdf::class)]
#[CoversClass(ReminderStatus::class)]
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
            $mock,
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
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $reminder = $api->get(1234);

        self::assertInstanceOf(Reminder::class, $reminder);
        self::assertSame('1. Mahnung', $reminder->subject);
        self::assertSame('M-2026-0001', $reminder->reminderNumber);
    }

    #[Test]
    public function itCreatesReminderViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

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
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ReminderCreateOptions(invoiceId: 42);
        $opts->date = new DateTimeImmutable('2026-03-01');
        $opts->dueDays = 7;
        $opts->subject = '1. Mahnung';

        $reminder = $api->create($opts);

        self::assertSame(777, $reminder->id);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(42, $payload['reminder']['invoice_id']);
        self::assertSame('1. Mahnung', $payload['reminder']['subject']);
        self::assertSame(7, $payload['reminder']['due_days']);
    }

    #[Test]
    public function itUpdatesReminder(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'reminder' => ['id' => 777, 'client_id' => 123, 'subject' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ReminderUpdateOptions();
        $opts->subject = 'Geändert';

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->subject);
        self::assertSame('PUT', $captured['method']);
    }

    #[Test]
    public function itPerformsStatusActions(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->complete(1));
        self::assertTrue($api->cancel(2));
        self::assertTrue($api->delete(3));

        self::assertSame('https://mycompany.billomat.net/api/reminders/1/complete', $captured[0]['url']);
        self::assertSame('https://mycompany.billomat.net/api/reminders/2/cancel', $captured[1]['url']);
        self::assertSame('DELETE', $captured[2]['method']);
    }

    #[Test]
    public function itEmailsReminder(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ReminderEmailOptions();
        $opts->to = ['kunde@example.com'];

        self::assertTrue($api->email(42, $opts));

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(['kunde@example.com'], $payload['email']['recipients']['to'] ?? null);
    }

    #[Test]
    public function itFetchesReminderPdf(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

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
            $mock,
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
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            return new MockResponse('%PDF-1.4 RAW%', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->pdf(777, InvoicePdfType::PRINT, rawPdf: true);

        self::assertIsString($result);
        self::assertStringContainsString('format=pdf', $captured['url']);
        self::assertStringContainsString('type=print', $captured['url']);
    }

    #[Test]
    public function itUploadsSignaturePdf(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new RemindersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->uploadSignature(555, base64_encode('PDF')));
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/reminders/555/upload-signature', $captured['url']);
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
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->complete(88);
    }
}
