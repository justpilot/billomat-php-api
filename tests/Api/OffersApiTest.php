<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferEmailOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\OfferUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Enum\OfferStatus;
use Justpilot\Billomat\Model\Offer;
use Justpilot\Billomat\Model\OfferPdf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(OffersApi::class)]
#[CoversClass(OfferCreateOptions::class)]
#[CoversClass(OfferUpdateOptions::class)]
#[CoversClass(OfferEmailOptions::class)]
#[CoversClass(Offer::class)]
#[CoversClass(OfferPdf::class)]
#[CoversClass(OfferStatus::class)]
final class OffersApiTest extends TestCase
{
    #[Test]
    public function itListsOffersAndPassesFilters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'offers' => [
                    'offer' => [
                        [
                            'id' => 1,
                            'client_id' => 123,
                            'status' => OfferStatus::DRAFT->value,
                            'offer_number' => null,
                            'date' => '2026-01-01',
                            'currency_code' => 'EUR',
                            'total_gross' => 119.00,
                            'total_net' => 100.00,
                        ],
                        [
                            'id' => 2,
                            'client_id' => 456,
                            'status' => OfferStatus::ACCEPTED->value,
                            'offer_number' => 'AN-2026-0002',
                            'date' => '2026-01-02',
                            'currency_code' => 'EUR',
                            'total_gross' => 59.50,
                            'total_net' => 50.00,
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $offers = $api->list(['per_page' => 50]);

        self::assertCount(2, $offers);
        self::assertContainsOnlyInstancesOf(Offer::class, $offers);
        self::assertSame(OfferStatus::DRAFT, $offers[0]->status);
        self::assertSame(OfferStatus::ACCEPTED, $offers[1]->status);

        $parts = parse_url($captured['url']);
        self::assertSame('/api/offers', $parts['path'] ?? null);

        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame(50, (int) ($query['per_page'] ?? 0));
    }

    #[Test]
    public function itGetsSingleOfferById(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
            $body = json_encode([
                'offer' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'status' => OfferStatus::OPEN->value,
                    'offer_number' => 'AN-2026-0001',
                    'date' => '2026-02-01',
                    'validity_days' => 14,
                    'currency_code' => 'EUR',
                    'total_gross' => 238.00,
                    'total_net' => 200.00,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $offer = $api->get(1234);

        self::assertInstanceOf(Offer::class, $offer);
        self::assertSame(1234, $offer->id);
        self::assertSame(OfferStatus::OPEN, $offer->status);
        self::assertSame('AN-2026-0001', $offer->offerNumber);
        self::assertSame(14, $offer->validityDays);
    }

    #[Test]
    public function itReturnsNullWhenOfferNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertNull($api->get(999999));
    }

    #[Test]
    public function itCreatesOfferDraftViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'offer' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => OfferStatus::DRAFT->value,
                    'offer_number' => null,
                    'date' => '2026-03-01',
                    'validity_days' => 30,
                    'currency_code' => 'EUR',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new OfferCreateOptions(clientId: 123);
        $opts->date = new DateTimeImmutable('2026-03-01');
        $opts->validityDays = 30;
        $opts->currencyCode = 'EUR';
        $opts->title = 'Angebot März';

        $item = new OfferItemCreateOptions(quantity: 2.0, unitPrice: 100.0);
        $item->title = 'Beratung';
        $opts->addItem($item);

        $created = $api->create($opts);

        self::assertInstanceOf(Offer::class, $created);
        self::assertSame(777, $created->id);
        self::assertSame(OfferStatus::DRAFT, $created->status);

        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offers', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertArrayHasKey('offer', $payload);
        self::assertSame(123, $payload['offer']['client_id']);
        self::assertSame('2026-03-01', $payload['offer']['date']);
        self::assertSame(30, $payload['offer']['validity_days']);
        self::assertSame('Angebot März', $payload['offer']['title']);

        self::assertArrayHasKey('offer-items', $payload['offer']);
        self::assertCount(1, $payload['offer']['offer-items']['offer-item']);
        self::assertSame(2.0, $payload['offer']['offer-items']['offer-item'][0]['quantity']);
    }

    #[Test]
    public function itUpdatesOfferDraftViaPut(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'offer' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => 'DRAFT',
                    'date' => '2026-03-10',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new OfferUpdateOptions();
        $opts->date = new DateTimeImmutable('2026-03-10');

        $updated = $api->update(777, $opts);

        self::assertInstanceOf(Offer::class, $updated);
        self::assertSame('2026-03-10', $updated->date?->format('Y-m-d'));

        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offers/777', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertSame(['offer' => ['date' => '2026-03-10']], $payload);
    }

    #[Test]
    public function itCompletesOfferViaPut(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->complete(777, 5));
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offers/777/complete', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertSame(['offer' => ['template_id' => 5]], $payload);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function statusActionProvider(): iterable
    {
        yield 'cancel' => ['cancel', 'cancel'];
        yield 'win' => ['win', 'win'];
        yield 'lose' => ['lose', 'lose'];
        yield 'clear' => ['clear', 'clear'];
        yield 'undo' => ['undo', 'undo'];
    }

    #[Test]
    public function itDeletesOfferViaDelete(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(777));
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offers/777', $captured['url']);
    }

    #[Test]
    public function itPerformsStatusActions(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->cancel(1));
        self::assertTrue($api->win(2));
        self::assertTrue($api->lose(3));
        self::assertTrue($api->clear(4));
        self::assertTrue($api->undo(5));

        self::assertCount(5, $captured);
        self::assertSame('https://mycompany.billomat.net/api/offers/1/cancel', $captured[0]['url']);
        self::assertSame('https://mycompany.billomat.net/api/offers/2/win', $captured[1]['url']);
        self::assertSame('https://mycompany.billomat.net/api/offers/3/lose', $captured[2]['url']);
        self::assertSame('https://mycompany.billomat.net/api/offers/4/clear', $captured[3]['url']);
        self::assertSame('https://mycompany.billomat.net/api/offers/5/undo', $captured[4]['url']);

        foreach ($captured as $row) {
            self::assertSame('PUT', $row['method']);
        }
    }

    #[Test]
    public function itEmailsOfferWithDefaults(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->email(42));
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offers/42/email', $captured['url']);
        self::assertSame(['email' => []], $this->extractJsonPayload($captured['options']));
    }

    #[Test]
    public function itEmailsOfferWithRecipients(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new OfferEmailOptions();
        $opts->from = 'me@example.com';
        $opts->to = ['kunde@example.com'];
        $opts->subject = 'Ihr Angebot';

        self::assertTrue($api->email(42, $opts));

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertSame('me@example.com', $payload['email']['from'] ?? null);
        self::assertSame(['kunde@example.com'], $payload['email']['recipients']['to'] ?? null);
        self::assertSame('Ihr Angebot', $payload['email']['subject'] ?? null);
    }

    #[Test]
    public function itFetchesOfferPdfInJsonMode(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'pdf' => [
                    'id' => 4882,
                    'created' => '2026-01-15T12:04:15+01:00',
                    'offer_id' => 240,
                    'filename' => 'offer_123.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 70137,
                    'base64file' => base64_encode('%PDF-FAKE%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $pdf = $api->pdf(240);

        self::assertInstanceOf(OfferPdf::class, $pdf);
        self::assertSame(4882, $pdf->id);
        self::assertSame(240, $pdf->offerId);
        self::assertSame('offer_123.pdf', $pdf->filename);
        self::assertStringStartsWith('%PDF-FAKE%', $pdf->getBinary());
        self::assertSame('https://mycompany.billomat.net/api/offers/240/pdf', $captured['url']);
    }

    #[Test]
    public function itFetchesRawOfferPdfBinary(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            return new MockResponse('%PDF-1.4 RAW%', [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/pdf'],
            ]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->pdf(id: 777, type: InvoicePdfType::PRINT, rawPdf: true);

        self::assertIsString($result);
        self::assertStringStartsWith('%PDF-1.4', $result);
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

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $base64 = base64_encode('PDF-FAKE');
        self::assertTrue($api->uploadSignature(555, $base64));

        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offers/555/upload-signature', $captured['url']);
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
    public function winMapsHttpErrorToValidationException(): void
    {
        // Regression: vor Bug-Fix verschluckte `win()` 4xx/5xx und gab still `false` zurück.
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['errors' => ['error' => 'Offer cannot be marked as won.']], JSON_THROW_ON_ERROR),
            ['http_code' => 422],
        ));

        $api = new OffersApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->win(42);
    }
}
