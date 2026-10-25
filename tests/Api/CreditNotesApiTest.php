<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\CreditNoteUpdateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\CreditNote;
use Justpilot\Billomat\Model\CreditNotePdf;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;
use Justpilot\Billomat\Model\Enum\PdfType;
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

#[CoversClass(CreditNotesApi::class)]
#[CoversClass(CreditNoteCreateOptions::class)]
#[CoversClass(CreditNoteUpdateOptions::class)]
#[CoversClass(EmailOptions::class)]
#[CoversClass(CreditNote::class)]
#[CoversClass(CreditNotePdf::class)]
#[CoversClass(CreditNoteStatus::class)]
#[UsesClass(CreditNoteItemCreateOptions::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ListCaster::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class CreditNotesApiTest extends TestCase
{
    #[Test]
    public function itListsCreditNotes(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

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
            new Psr18Client($mock),
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
                    'open_amount' => '50',
                    'paid_amount' => '0',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $note = $api->get(1234);

        self::assertInstanceOf(CreditNote::class, $note);
        self::assertSame(CreditNoteStatus::OPEN, $note->status);
        self::assertSame('GS-2026-0001', $note->creditNoteNumber);
        self::assertSame(42, $note->invoiceId);
        self::assertSame('50', $note->openAmount?->toString());
    }

    #[Test]
    public function itReturnsNullWhenNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertNull($api->get(999999));
    }

    #[Test]
    public function itCreatesCreditNoteViaPost(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

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
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new CreditNoteCreateOptions(
            clientId: 123,
            date: new DateTimeImmutable('2026-03-01'),
            title: 'Korrektur',
            invoiceId: 99,
        );

        $item = new CreditNoteItemCreateOptions(quantity: 1.0, unitPrice: Money::of('50'));
        $opts = $opts->withItem($item);

        $created = $api->create($opts);

        self::assertSame(777, $created->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/credit-notes', $captured->url);

        self::assertSame(99, $captured->jsonAt('credit-note', 'invoice_id'));
        self::assertCount(1, $captured->jsonArray('credit-note', 'credit-note-items', 'credit-note-item'));
    }

    #[Test]
    public function itUpdatesCreditNote(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'credit-note' => ['id' => 777, 'client_id' => 123, 'title' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new CreditNoteUpdateOptions(title: 'Geändert');

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->title);
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

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $api->complete(1, 5);
        $api->cancel(2);
        $api->uncancel(3);
        $api->delete(4);

        self::assertSame('https://mycompany.billomat.net/api/credit-notes/1/complete', $captured[0]->url);
        self::assertSame('https://mycompany.billomat.net/api/credit-notes/2/cancel', $captured[1]->url);
        self::assertSame('https://mycompany.billomat.net/api/credit-notes/3/uncancel', $captured[2]->url);
        self::assertSame('DELETE', $captured[3]->method);
    }

    #[Test]
    public function itEmailsCreditNote(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new EmailOptions(from: 'me@example.com', to: ['kunde@example.com']);

        $api->email(42, $opts);

        self::assertSame(['kunde@example.com'], $captured->jsonAt('email', 'recipients', 'to'));
    }

    #[Test]
    public function itFetchesCreditNotePdfInJsonMode(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

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
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $pdf = $api->pdf(240);

        self::assertInstanceOf(CreditNotePdf::class, $pdf);
        self::assertSame(240, $pdf->creditNoteId);
        self::assertStringStartsWith('%PDF-FAKE%', $pdf->getBinary());
        self::assertSame('https://mycompany.billomat.net/api/credit-notes/240/pdf', $captured->url);
    }

    #[Test]
    public function itFetchesRawPdf(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            return new MockResponse('%PDF-1.4 RAW%', ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->pdf(777, PdfType::SIGNED, rawPdf: true);

        self::assertIsString($result);
        self::assertStringContainsString('format=pdf', $captured->url);
        self::assertStringContainsString('type=signed', $captured->url);
    }

    #[Test]
    public function itUploadsSignaturePdf(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $base64 = base64_encode('PDF-FAKE');
        $api->uploadSignature(555, $base64);
        self::assertSame('PUT', $captured->method);
    }

    #[Test]
    public function cancelMapsHttpErrorToValidationException(): void
    {
        // Regression: vor Bug-Fix verschluckte `cancel()` 4xx/5xx und gab still `false` zurück.
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['errors' => ['error' => 'Credit note cannot be cancelled.']], JSON_THROW_ON_ERROR),
            ['http_code' => 422],
        ));

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->cancel(9);
    }
}
