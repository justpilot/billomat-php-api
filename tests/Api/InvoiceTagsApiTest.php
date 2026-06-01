<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\Api\InvoiceTagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\InvoiceTag;
use Justpilot\Billomat\Model\InvoiceTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(InvoiceTagsApi::class)]
#[CoversClass(InvoiceTag::class)]
#[CoversClass(InvoiceTagCloudEntry::class)]
#[CoversClass(InvoiceTagCreateOptions::class)]
final class InvoiceTagsApiTest extends TestCase
{
    #[Test]
    public function listByInvoiceSendsMandatoryInvoiceIdAndParsesTags(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'invoice-tags' => [
                    'invoice-tag' => [
                        ['id' => 1, 'invoice_id' => 100, 'name' => 'wichtig'],
                        ['id' => 2, 'invoice_id' => 100, 'name' => 'A-Kunde'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceTagsApi($http);

        $tags = $api->listByInvoice(100);

        self::assertCount(2, $tags);
        self::assertContainsOnlyInstancesOf(InvoiceTag::class, $tags);
        self::assertSame('wichtig', $tags[0]->name);

        $parts = parse_url((string) $captured['url']);
        self::assertSame('/api/invoice-tags', $parts['path'] ?? null);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('100', $query['invoice_id'] ?? null);
    }

    #[Test]
    public function cloudReturnsAggregatedEntries(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'invoice-tags' => [
                    'tag' => [
                        ['id' => 1, 'name' => 'wichtig', 'count' => 5],
                        ['id' => 2, 'name' => 'A-Kunde', 'count' => 2],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceTagsApi($http);

        $cloud = $api->cloud();

        self::assertCount(2, $cloud);
        self::assertContainsOnlyInstancesOf(InvoiceTagCloudEntry::class, $cloud);
        self::assertSame(5, $cloud[0]->count);
    }

    #[Test]
    public function cloudNormalizesSingleEntry(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'invoice-tags' => [
                    'tag' => ['id' => 1, 'name' => 'solo', 'count' => 1],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceTagsApi($http);

        $cloud = $api->cloud();

        self::assertCount(1, $cloud);
        self::assertSame('solo', $cloud[0]->name);
    }

    #[Test]
    public function getReturnsNullOnNotFound(): void
    {
        $mock = new MockHttpClient([new MockResponse('', ['http_code' => 404])]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceTagsApi($http);

        self::assertNull($api->get(999));
    }

    #[Test]
    public function createPostsTagAndReturnsHydratedModel(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'invoice-tag' => ['id' => 33, 'invoice_id' => 100, 'name' => 'wichtig'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceTagsApi($http);

        $tag = $api->create(new InvoiceTagCreateOptions(invoiceId: 100, name: 'wichtig'));

        self::assertSame(33, $tag->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/invoice-tags', $captured['url']);
    }

    #[Test]
    public function deleteSendsDeleteRequest(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceTagsApi($http);

        self::assertTrue($api->delete(7));
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/invoice-tags/7', $captured['url']);
    }
}
