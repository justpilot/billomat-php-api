<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\ClientTagCreateOptions;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ClientTag;
use Justpilot\Billomat\Model\ClientTagCloudEntry;
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
#[UsesClass(ClientsApi::class)]
#[CoversClass(ClientTagCreateOptions::class)]
#[CoversClass(ClientTag::class)]
#[CoversClass(ClientTagCloudEntry::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ClientTagsApiTest extends TestCase
{
    #[Test]
    public function itHandlesListCloudCreateDelete(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            if (str_contains($url, '/client-tags?client_id=42')) {
                return new MockResponse(
                    json_encode([
                        'client-tags' => ['client-tag' => [['id' => 1, 'client_id' => 42, 'name' => 'VIP']]],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 200]
                );
            }

            if (str_ends_with($url, '/client-tags')) {
                if ('GET' === $method) {
                    return new MockResponse(
                        json_encode([
                            'client-tags' => ['tag' => [['id' => 1, 'name' => 'VIP', 'count' => 9]]],
                        ], JSON_THROW_ON_ERROR),
                        ['http_code' => 200]
                    );
                }

                return new MockResponse(
                    json_encode([
                        'client-tag' => ['id' => 99, 'client_id' => 42, 'name' => 'neu'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ClientsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        self::assertCount(1, $api->list(42));

        $cloud = $api->cloud();
        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(ClientTagCloudEntry::class, $cloud);

        $tag = $api->create(new ClientTagCreateOptions(clientId: 42, name: 'neu'));
        self::assertSame(99, $tag->id);

        $api->delete(99);
    }
}
