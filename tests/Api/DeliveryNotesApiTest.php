<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteEmailOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\DeliveryNoteUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\DeliveryNote;
use Justpilot\Billomat\Model\DeliveryNotePdf;
use Justpilot\Billomat\Model\Enum\DeliveryNoteStatus;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(DeliveryNotesApi::class)]
#[CoversClass(DeliveryNoteCreateOptions::class)]
#[CoversClass(DeliveryNoteUpdateOptions::class)]
#[CoversClass(DeliveryNoteEmailOptions::class)]
#[CoversClass(DeliveryNote::class)]
#[CoversClass(DeliveryNotePdf::class)]
#[CoversClass(DeliveryNoteStatus::class)]
final class DeliveryNotesApiTest extends TestCase
{
    #[Test]
    public function itListsDeliveryNotes(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'delivery-notes' => [
                    'delivery-note' => [
                        [
                            'id' => 1,
                            'client_id' => 123,
                            'status' => DeliveryNoteStatus::DRAFT->value,
                            'delivery_note_number' => null,
                            'date' => '2026-01-01',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $notes = $api->list(['per_page' => 5]);

        self::assertCount(1, $notes);
        self::assertContainsOnlyInstancesOf(DeliveryNote::class, $notes);

        $parts = parse_url($captured['url']);
        self::assertSame('/api/delivery-notes', $parts['path'] ?? null);
    }

    #[Test]
    public function itGetsSingleDeliveryNote(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'delivery-note' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'status' => DeliveryNoteStatus::OPEN->value,
                    'delivery_note_number' => 'LS-2026-0001',
                    'invoice_id' => 42,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $note = $api->get(1234);

        self::assertInstanceOf(DeliveryNote::class, $note);
        self::assertSame(DeliveryNoteStatus::OPEN, $note->status);
        self::assertSame('LS-2026-0001', $note->deliveryNoteNumber);
        self::assertSame(42, $note->invoiceId);
    }

    #[Test]
    public function itReturnsNullWhenNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertNull($api->get(999999));
    }

    #[Test]
    public function itCreatesDeliveryNoteViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'delivery-note' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => DeliveryNoteStatus::DRAFT->value,
                    'date' => '2026-03-01',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new DeliveryNoteCreateOptions(clientId: 123);
        $opts->date = new DateTimeImmutable('2026-03-01');
        $opts->invoiceId = 99;

        $item = new DeliveryNoteItemCreateOptions(quantity: 5.0, unitPrice: 0.0);
        $item->title = 'Lieferung';
        $opts->addItem($item);

        $created = $api->create($opts);

        self::assertSame(777, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertSame(123, $payload['delivery-note']['client_id']);
        self::assertSame(99, $payload['delivery-note']['invoice_id']);
        self::assertCount(1, $payload['delivery-note']['delivery-note-items']['delivery-note-item']);
    }

    #[Test]
    public function itUpdatesDeliveryNoteViaPut(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'delivery-note' => ['id' => 777, 'client_id' => 123, 'title' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new DeliveryNoteUpdateOptions();
        $opts->title = 'Geändert';

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->title);
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/777', $captured['url']);
    }

    #[Test]
    public function itPerformsStatusActions(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->complete(1));
        self::assertTrue($api->cancel(2));
        self::assertTrue($api->clear(3));
        self::assertTrue($api->undo(4));
        self::assertTrue($api->delete(5));

        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/1/complete', $captured[0]['url']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/2/cancel', $captured[1]['url']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/3/clear', $captured[2]['url']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/4/undo', $captured[3]['url']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/5', $captured[4]['url']);
        self::assertSame('DELETE', $captured[4]['method']);
    }

    #[Test]
    public function itEmailsDeliveryNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new DeliveryNoteEmailOptions();
        $opts->from = 'me@example.com';
        $opts->to = ['kunde@example.com'];

        self::assertTrue($api->email(42, $opts));

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertSame('me@example.com', $payload['email']['from'] ?? null);
    }

    #[Test]
    public function itFetchesDeliveryNotePdfInJsonMode(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'pdf' => [
                    'id' => 4882,
                    'delivery_note_id' => 240,
                    'filename' => 'ls_123.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 70137,
                    'base64file' => base64_encode('%PDF-FAKE%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $pdf = $api->pdf(240);

        self::assertInstanceOf(DeliveryNotePdf::class, $pdf);
        self::assertSame(240, $pdf->deliveryNoteId);
        self::assertStringStartsWith('%PDF-FAKE%', $pdf->getBinary());
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/240/pdf', $captured['url']);
    }

    #[Test]
    public function itFetchesRawPdfAndAcceptsType(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            return new MockResponse('%PDF-1.4 RAW%', ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
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
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $base64 = base64_encode('PDF-FAKE');
        self::assertTrue($api->uploadSignature(555, $base64));

        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/delivery-notes/555/upload-signature', $captured['url']);
        self::assertSame(['upload' => ['base64file' => $base64]], $this->extractJsonPayload($captured['options']));
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

    #[Test]
    public function undoMapsHttpErrorToValidationException(): void
    {
        // Regression: vor Bug-Fix verschluckte `undo()` 4xx/5xx und gab still `false` zurück.
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['errors' => ['error' => 'Delivery note status cannot be reverted.']], JSON_THROW_ON_ERROR),
            ['http_code' => 422],
        ));

        $api = new DeliveryNotesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->undo(21);
    }
}
