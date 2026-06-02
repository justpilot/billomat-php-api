<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteEmailOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\CreditNoteUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNotePdf;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CreditNotesApi::class)]
#[CoversClass(CreditNoteCreateOptions::class)]
#[CoversClass(CreditNoteUpdateOptions::class)]
#[CoversClass(CreditNoteEmailOptions::class)]
#[CoversClass(CreditNote::class)]
#[CoversClass(CreditNotePdf::class)]
#[CoversClass(CreditNoteStatus::class)]
final class CreditNotesApiTest extends TestCase
{
    #[Test]
    public function itListsCreditNotes(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'credit-notes' => [
                    'credit-note' => [
                        ['id' => 1, 'client_id' => 123, 'status' => CreditNoteStatus::DRAFT->value, 'date' => '2026-01-01'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $notes = $api->list(['per_page' => 5]);

        self::assertCount(1, $notes);
        self::assertContainsOnlyInstancesOf(CreditNote::class, $notes);
        self::assertSame(CreditNoteStatus::DRAFT, $notes[0]->status);
    }

    #[Test]
    public function itGetsSingleCreditNote(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'credit-note' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'status' => CreditNoteStatus::OPEN->value,
                    'credit_note_number' => 'GS-2026-0001',
                    'invoice_id' => 42,
                    'open_amount' => 50.0,
                    'paid_amount' => 0.0,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $note = $api->get(1234);

        self::assertInstanceOf(CreditNote::class, $note);
        self::assertSame(CreditNoteStatus::OPEN, $note->status);
        self::assertSame('GS-2026-0001', $note->creditNoteNumber);
        self::assertSame(42, $note->invoiceId);
        self::assertSame(50.0, $note->openAmount);
    }

    #[Test]
    public function itReturnsNullWhenNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertNull($api->get(999999));
    }

    #[Test]
    public function itCreatesCreditNoteViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'credit-note' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => CreditNoteStatus::DRAFT->value,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new CreditNoteCreateOptions(clientId: 123);
        $opts->date = new DateTimeImmutable('2026-03-01');
        $opts->invoiceId = 99;
        $opts->title = 'Korrektur';

        $item = new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: 50.0);
        $opts->addItem($item);

        $created = $api->create($opts);

        self::assertSame(777, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/credit-notes', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertSame(99, $payload['credit-note']['invoice_id']);
        self::assertCount(1, $payload['credit-note']['credit-note-items']['credit-note-item']);
    }

    #[Test]
    public function itUpdatesCreditNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'credit-note' => ['id' => 777, 'client_id' => 123, 'title' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new CreditNoteUpdateOptions();
        $opts->title = 'Geändert';

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->title);
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

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->complete(1, 5));
        self::assertTrue($api->cancel(2));
        self::assertTrue($api->uncancel(3));
        self::assertTrue($api->delete(4));

        self::assertSame('https://mycompany.billomat.net/api/credit-notes/1/complete', $captured[0]['url']);
        self::assertSame('https://mycompany.billomat.net/api/credit-notes/2/cancel', $captured[1]['url']);
        self::assertSame('https://mycompany.billomat.net/api/credit-notes/3/uncancel', $captured[2]['url']);
        self::assertSame('DELETE', $captured[3]['method']);
    }

    #[Test]
    public function itEmailsCreditNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new CreditNoteEmailOptions();
        $opts->from = 'me@example.com';
        $opts->to = ['kunde@example.com'];

        self::assertTrue($api->email(42, $opts));

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertSame(['kunde@example.com'], $payload['email']['recipients']['to'] ?? null);
    }

    #[Test]
    public function itFetchesCreditNotePdfInJsonMode(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'pdf' => [
                    'id' => 4882,
                    'credit_note_id' => 240,
                    'filename' => 'gs_123.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 70137,
                    'base64file' => base64_encode('%PDF-FAKE%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $pdf = $api->pdf(240);

        self::assertInstanceOf(CreditNotePdf::class, $pdf);
        self::assertSame(240, $pdf->creditNoteId);
        self::assertStringStartsWith('%PDF-FAKE%', $pdf->getBinary());
        self::assertSame('https://mycompany.billomat.net/api/credit-notes/240/pdf', $captured['url']);
    }

    #[Test]
    public function itFetchesRawPdf(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            return new MockResponse('%PDF-1.4 RAW%', ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->pdf(777, InvoicePdfType::SIGNED, rawPdf: true);

        self::assertIsString($result);
        self::assertStringContainsString('format=pdf', $captured['url']);
        self::assertStringContainsString('type=signed', $captured['url']);
    }

    #[Test]
    public function itUploadsSignaturePdf(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $base64 = base64_encode('PDF-FAKE');
        self::assertTrue($api->uploadSignature(555, $base64));
        self::assertSame('PUT', $captured['method']);
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return array<string,mixed>|null
     */
    private function extractJsonPayload(array $options): ?array
    {
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body']) && '' !== $options['body']) {
            $decoded = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);

            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        return \is_array($payload) ? $payload : null;
    }
}
