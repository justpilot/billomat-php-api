<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\SuppliersApi;
use Justpilot\Billomat\Api\SupplierTagCreateOptions;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\SupplierTag;
use Justpilot\Billomat\Model\SupplierTagCloudEntry;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(TagsApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[UsesClass(SuppliersApi::class)]
#[CoversClass(SupplierTagCreateOptions::class)]
#[CoversClass(SupplierTag::class)]
#[CoversClass(SupplierTagCloudEntry::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class SupplierTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsBySupplier(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'supplier-tags' => [
                    'supplier-tag' => [['id' => 1, 'supplier_id' => 42, 'name' => 'IT']],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tags = $api->list(42);
        self::assertCount(1, $tags);
        self::assertSame('IT', $tags[0]->name);
        self::assertStringContainsString('supplier_id=42', $captured->url);
    }

    #[Test]
    public function itLoadsCloud(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'supplier-tags' => ['tag' => [['id' => 1, 'name' => 'IT', 'count' => 3]]],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $cloud = $api->cloud();
        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(SupplierTagCloudEntry::class, $cloud);
    }

    #[Test]
    public function itCreatesAndDeletesTag(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = CapturedRequest::of($method, $url);

            if ('POST' === $method) {
                return new MockResponse(
                    json_encode([
                        'supplier-tag' => ['id' => 99, 'supplier_id' => 42, 'name' => 'neu'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new SuppliersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tag = $api->create(42, new SupplierTagCreateOptions(name: 'neu'));
        self::assertSame(99, $tag->id);
        $api->delete(99);
    }
}
