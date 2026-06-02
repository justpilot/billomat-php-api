<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ClientTagCreateOptions;
use Justpilot\Billomat\Api\ClientTagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\ClientTag;
use Justpilot\Billomat\Model\ClientTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ClientTagsApi::class)]
#[CoversClass(ClientTagCreateOptions::class)]
#[CoversClass(ClientTag::class)]
#[CoversClass(ClientTagCloudEntry::class)]
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

        $api = new ClientTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertCount(1, $api->listByClient(42));

        $cloud = $api->cloud();
        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(ClientTagCloudEntry::class, $cloud);

        $tag = $api->create(new ClientTagCreateOptions(clientId: 42, name: 'neu'));
        self::assertSame(99, $tag->id);

        self::assertTrue($api->delete(99));
    }
}
