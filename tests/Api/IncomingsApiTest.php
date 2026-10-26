<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingsApi;
use Justpilot\Billomat\Api\IncomingUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Justpilot\Billomat\Model\Incoming;
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

#[CoversClass(IncomingsApi::class)]
#[CoversClass(IncomingCreateOptions::class)]
#[CoversClass(IncomingUpdateOptions::class)]
#[CoversClass(Incoming::class)]
#[CoversClass(IncomingStatus::class)]
#[UsesClass(ListCaster::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class IncomingsApiTest extends TestCase
{
    #[Test]
    public function itListsIncomings(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'incomings' => [
                    'incoming' => [
                        ['id' => 1, 'supplier_id' => 42, 'status' => 'OPEN', 'total_gross' => '119'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $incomings = $api->list();
        self::assertCount(1, $incomings);
        self::assertSame(IncomingStatus::OPEN, $incomings[0]->status);
        self::assertSame('119', $incomings[0]->totalGross?->toString());
    }

    #[Test]
    public function itGetsSingleIncoming(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'incoming' => [
                    'id' => 1234,
                    'supplier_id' => 42,
                    'incoming_number' => 'IN-2026-0001',
                    'status' => 'PAID',
                    'paid_amount' => '100',
                    'open_amount' => '0',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $incoming = $api->get(1234);
        self::assertInstanceOf(Incoming::class, $incoming);
        self::assertSame(IncomingStatus::PAID, $incoming->status);
        self::assertSame('IN-2026-0001', $incoming->incomingNumber);
    }

    #[Test]
    public function itCreatesIncoming(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'incoming' => ['id' => 777, 'supplier_id' => 42, 'status' => 'OPEN'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new IncomingCreateOptions(
            supplierId: 42,
            date: new DateTimeImmutable('2026-03-01'),
            totalGross: Money::of('119'),
            totalNet: Money::of('100'),
        );

        $created = $api->create($opts);
        self::assertSame(777, $created->id);

        self::assertSame(42, $captured->jsonAt('incoming', 'supplier_id'));
        self::assertSame('119', $captured->jsonAt('incoming', 'total_gross'));
    }

    #[Test]
    public function itUpdatesIncoming(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            $body = json_encode([
                'incoming' => ['id' => 777, 'supplier_id' => 42, 'label' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new IncomingUpdateOptions(label: 'Geändert');

        $updated = $api->update(777, $opts);
        self::assertSame('Geändert', $updated->label);
        self::assertSame('PUT', $captured->method);
    }

    #[Test]
    public function itPerformsStatusActionsAndUpload(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured[] = CapturedRequest::of($method, $url);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $api->cancel(1);
        $api->uncancel(2);
        $api->upload(3, base64_encode('PDF'));
        $api->delete(4);

        self::assertSame('https://mycompany.billomat.net/api/incomings/1/cancel', $captured[0]->url);
        self::assertSame('https://mycompany.billomat.net/api/incomings/2/uncancel', $captured[1]->url);
        self::assertSame('https://mycompany.billomat.net/api/incomings/3/upload', $captured[2]->url);
        self::assertSame('DELETE', $captured[3]->method);
    }

    #[Test]
    public function cancelMapsHttpErrorToValidationException(): void
    {
        // Regression: vor Bug-Fix verschluckte `cancel()` 4xx/5xx und gab still `false` zurück.
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['errors' => ['error' => 'Incoming cannot be cancelled.']], JSON_THROW_ON_ERROR),
            ['http_code' => 422],
        ));

        $api = new IncomingsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->cancel(12);
    }
}
