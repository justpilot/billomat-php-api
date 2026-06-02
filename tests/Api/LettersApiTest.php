<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\LetterEmailOptions;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\LetterUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Enum\LetterStatus;
use Justpilot\Billomat\Model\Letter;
use Justpilot\Billomat\Model\LetterPdf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(LettersApi::class)]
#[CoversClass(LetterCreateOptions::class)]
#[CoversClass(LetterUpdateOptions::class)]
#[CoversClass(LetterEmailOptions::class)]
#[CoversClass(Letter::class)]
#[CoversClass(LetterPdf::class)]
#[CoversClass(LetterStatus::class)]
final class LettersApiTest extends TestCase
{
    #[Test]
    public function itListsLetters(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'letters' => [
                    'letter' => [
                        ['id' => 1, 'client_id' => 123, 'status' => 'DRAFT', 'subject' => 'Anschreiben'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $letters = $api->list();

        self::assertCount(1, $letters);
        self::assertSame(LetterStatus::DRAFT, $letters[0]->status);
        self::assertSame('Anschreiben', $letters[0]->subject);
    }

    #[Test]
    public function itGetsSingleLetter(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'letter' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'status' => 'OPEN',
                    'letter_number' => 'B-2026-0001',
                    'subject' => 'Information',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $letter = $api->get(1234);

        self::assertInstanceOf(Letter::class, $letter);
        self::assertSame(LetterStatus::OPEN, $letter->status);
        self::assertSame('B-2026-0001', $letter->letterNumber);
    }

    #[Test]
    public function itCreatesLetterViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'letter' => ['id' => 777, 'client_id' => 123, 'status' => 'DRAFT', 'subject' => 'Info'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new LetterCreateOptions(clientId: 123);
        $opts->date = new DateTimeImmutable('2026-03-01');
        $opts->subject = 'Info';

        $letter = $api->create($opts);

        self::assertSame(777, $letter->id);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(123, $payload['letter']['client_id']);
        self::assertSame('Info', $payload['letter']['subject']);
    }

    #[Test]
    public function itUpdatesLetter(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'letter' => ['id' => 777, 'client_id' => 123, 'subject' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new LetterUpdateOptions();
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

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->complete(1));
        self::assertTrue($api->cancel(2));
        self::assertTrue($api->clear(3));
        self::assertTrue($api->undo(4));
        self::assertTrue($api->delete(5));

        self::assertSame('https://mycompany.billomat.net/api/letters/1/complete', $captured[0]['url']);
        self::assertSame('https://mycompany.billomat.net/api/letters/2/cancel', $captured[1]['url']);
        self::assertSame('https://mycompany.billomat.net/api/letters/3/clear', $captured[2]['url']);
        self::assertSame('https://mycompany.billomat.net/api/letters/4/undo', $captured[3]['url']);
        self::assertSame('DELETE', $captured[4]['method']);
    }

    #[Test]
    public function itEmailsLetter(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new LetterEmailOptions();
        $opts->to = ['kunde@example.com'];

        self::assertTrue($api->email(42, $opts));

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(['kunde@example.com'], $payload['email']['recipients']['to'] ?? null);
    }

    #[Test]
    public function itFetchesLetterPdf(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'pdf' => [
                    'id' => 4882,
                    'letter_id' => 240,
                    'filename' => 'brief.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 70137,
                    'base64file' => base64_encode('%PDF-FAKE%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $pdf = $api->pdf(240);

        self::assertInstanceOf(LetterPdf::class, $pdf);
        self::assertSame(240, $pdf->letterId);
    }

    #[Test]
    public function itFetchesRawPdf(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            return new MockResponse('%PDF-1.4 RAW%', ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->pdf(777, InvoicePdfType::PRINT, rawPdf: true);

        self::assertIsString($result);
        self::assertStringContainsString('format=pdf', $captured['url']);
    }

    #[Test]
    public function itUploadsLetterPdfAndSignature(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $b64 = base64_encode('PDF');

        self::assertTrue($api->upload(100, $b64));
        self::assertTrue($api->uploadSignature(200, $b64));

        self::assertSame('https://mycompany.billomat.net/api/letters/100/upload', $captured[0]['url']);
        self::assertSame('https://mycompany.billomat.net/api/letters/200/upload-signature', $captured[1]['url']);
    }
}
