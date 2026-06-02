<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingsApi;
use Justpilot\Billomat\Api\IncomingUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Justpilot\Billomat\Model\Incoming;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(IncomingsApi::class)]
#[CoversClass(IncomingCreateOptions::class)]
#[CoversClass(IncomingUpdateOptions::class)]
#[CoversClass(Incoming::class)]
#[CoversClass(IncomingStatus::class)]
final class IncomingsApiTest extends TestCase
{
    #[Test]
    public function itListsIncomings(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'incomings' => [
                    'incoming' => [
                        ['id' => 1, 'supplier_id' => 42, 'status' => 'OPEN', 'total_gross' => 119.0],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $incomings = $api->list();
        self::assertCount(1, $incomings);
        self::assertSame(IncomingStatus::OPEN, $incomings[0]->status);
        self::assertSame(119.0, $incomings[0]->totalGross);
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
                    'paid_amount' => 100.0,
                    'open_amount' => 0.0,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            $mock,
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
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'incoming' => ['id' => 777, 'supplier_id' => 42, 'status' => 'OPEN'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new IncomingCreateOptions(supplierId: 42);
        $opts->date = new DateTimeImmutable('2026-03-01');
        $opts->totalGross = 119.0;
        $opts->totalNet = 100.0;

        $created = $api->create($opts);
        self::assertSame(777, $created->id);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(42, $payload['incoming']['supplier_id']);
        self::assertSame(119.0, $payload['incoming']['total_gross']);
    }

    #[Test]
    public function itUpdatesIncoming(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'incoming' => ['id' => 777, 'supplier_id' => 42, 'label' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new IncomingUpdateOptions();
        $opts->label = 'Geändert';

        $updated = $api->update(777, $opts);
        self::assertSame('Geändert', $updated->label);
        self::assertSame('PUT', $captured['method']);
    }

    #[Test]
    public function itPerformsStatusActionsAndUpload(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new IncomingsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->cancel(1));
        self::assertTrue($api->uncancel(2));
        self::assertTrue($api->upload(3, base64_encode('PDF')));
        self::assertTrue($api->delete(4));

        self::assertSame('https://mycompany.billomat.net/api/incomings/1/cancel', $captured[0]['url']);
        self::assertSame('https://mycompany.billomat.net/api/incomings/2/uncancel', $captured[1]['url']);
        self::assertSame('https://mycompany.billomat.net/api/incomings/3/upload', $captured[2]['url']);
        self::assertSame('DELETE', $captured[3]['method']);
    }
}
