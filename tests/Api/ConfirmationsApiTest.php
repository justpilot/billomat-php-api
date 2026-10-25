<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\ConfirmationUpdateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Confirmation;
use Justpilot\Billomat\Model\ConfirmationPdf;
use Justpilot\Billomat\Model\Enum\ConfirmationStatus;
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

#[CoversClass(ConfirmationsApi::class)]
#[CoversClass(ConfirmationCreateOptions::class)]
#[CoversClass(ConfirmationUpdateOptions::class)]
#[CoversClass(EmailOptions::class)]
#[CoversClass(Confirmation::class)]
#[CoversClass(ConfirmationPdf::class)]
#[CoversClass(ConfirmationStatus::class)]
#[UsesClass(ConfirmationItemCreateOptions::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ListCaster::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class ConfirmationsApiTest extends TestCase
{
    #[Test]
    public function itListsConfirmationsAndPassesFilters(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'confirmations' => [
                    'confirmation' => [
                        [
                            'id' => 1,
                            'client_id' => 123,
                            'status' => ConfirmationStatus::DRAFT->value,
                            'confirmation_number' => null,
                            'date' => '2026-01-01',
                            'currency_code' => 'EUR',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $confirmations = $api->list(['per_page' => 5]);

        self::assertCount(1, $confirmations);
        self::assertContainsOnlyInstancesOf(Confirmation::class, $confirmations);
        self::assertSame(ConfirmationStatus::DRAFT, $confirmations[0]->status);

        $parts = parse_url($captured->url);
        self::assertSame('/api/confirmations', $parts['path'] ?? null);
    }

    #[Test]
    public function itGetsSingleConfirmationById(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'confirmation' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'status' => ConfirmationStatus::OPEN->value,
                    'confirmation_number' => 'AB-2026-0001',
                    'date' => '2026-02-01',
                    'offer_id' => 42,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $confirmation = $api->get(1234);

        self::assertInstanceOf(Confirmation::class, $confirmation);
        self::assertSame(1234, $confirmation->id);
        self::assertSame(ConfirmationStatus::OPEN, $confirmation->status);
        self::assertSame('AB-2026-0001', $confirmation->confirmationNumber);
        self::assertSame(42, $confirmation->offerId);
    }

    #[Test]
    public function itReturnsNullWhenNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertNull($api->get(999999));
    }

    #[Test]
    public function itCreatesConfirmationViaPost(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'confirmation' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => ConfirmationStatus::DRAFT->value,
                    'date' => '2026-03-01',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ConfirmationCreateOptions(
            clientId: 123,
            date: new DateTimeImmutable('2026-03-01'),
            title: 'Bestätigung März',
            offerId: 99,
        );

        $item = new ConfirmationItemCreateOptions(quantity: 1.0, unitPrice: Money::of('50'), title: 'Beratung');
        $opts = $opts->withItem($item);

        $created = $api->create($opts);

        self::assertSame(777, $created->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations', $captured->url);

        self::assertSame(123, $captured->jsonAt('confirmation', 'client_id'));
        self::assertSame(99, $captured->jsonAt('confirmation', 'offer_id'));
        self::assertCount(1, $captured->jsonArray('confirmation', 'confirmation-items', 'confirmation-item'));
    }

    #[Test]
    public function itUpdatesConfirmationViaPut(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'confirmation' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => 'DRAFT',
                    'title' => 'Geändert',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ConfirmationUpdateOptions(title: 'Geändert');

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->title);
        self::assertSame('PUT', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/777', $captured->url);
    }

    #[Test]
    public function itCompletesConfirmationWithTemplateId(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $api->complete(777, 5);
        self::assertSame('PUT', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/777/complete', $captured->url);

        $payload = $captured->json();
        self::assertSame(['confirmation' => ['template_id' => 5]], $payload);
    }

    #[Test]
    public function itPerformsStatusActions(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured[] = CapturedRequest::of($method, $url);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $api->cancel(1);
        $api->clear(2);
        $api->uncancel(3);
        $api->delete(4);
        $api->unclear(5);

        self::assertSame('PUT', $captured[0]->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/1/cancel', $captured[0]->url);
        self::assertSame('PUT', $captured[1]->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/2/clear', $captured[1]->url);
        self::assertSame('PUT', $captured[2]->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/3/uncancel', $captured[2]->url);
        self::assertSame('DELETE', $captured[3]->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/4', $captured[3]->url);
        self::assertSame('PUT', $captured[4]->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/5/unclear', $captured[4]->url);
    }

    #[Test]
    public function itEmailsConfirmation(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new EmailOptions(from: 'me@example.com', to: ['kunde@example.com']);

        $api->email(42, $opts);

        self::assertSame('me@example.com', $captured->jsonAt('email', 'from'));
        self::assertSame(['kunde@example.com'], $captured->jsonAt('email', 'recipients', 'to'));
    }

    #[Test]
    public function itFetchesConfirmationPdfInJsonMode(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'pdf' => [
                    'id' => 4882,
                    'confirmation_id' => 240,
                    'filename' => 'confirmation_123.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 70137,
                    'base64file' => base64_encode('%PDF-FAKE%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $pdf = $api->pdf(240);

        self::assertInstanceOf(ConfirmationPdf::class, $pdf);
        self::assertSame(240, $pdf->confirmationId);
        self::assertStringStartsWith('%PDF-FAKE%', $pdf->getBinary());
        self::assertSame('https://mycompany.billomat.net/api/confirmations/240/pdf', $captured->url);
    }

    #[Test]
    public function itFetchesRawConfirmationPdf(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            return new MockResponse('%PDF-1.4 RAW%', ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->pdf(777, PdfType::PRINT, rawPdf: true);

        self::assertIsString($result);
        self::assertStringStartsWith('%PDF-1.4', $result);
        self::assertStringContainsString('format=pdf', $captured->url);
        self::assertStringContainsString('type=print', $captured->url);
    }

    #[Test]
    public function itUploadsSignaturePdf(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $base64 = base64_encode('PDF-FAKE');
        $api->uploadSignature(555, $base64);

        self::assertSame('PUT', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/confirmations/555/upload-signature', $captured->url);
        self::assertSame(['upload' => ['base64file' => $base64]], $captured->json());
    }

    #[Test]
    public function clearMapsHttpErrorToValidationException(): void
    {
        // Regression: vor Bug-Fix verschluckte `clear()` 4xx/5xx und gab still `false` zurück.
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['errors' => ['error' => 'Confirmation cannot be cleared.']], JSON_THROW_ON_ERROR),
            ['http_code' => 422],
        ));

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->clear(33);
    }
}
