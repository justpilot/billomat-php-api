<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\IncomingTagCreateOptions;
use Justpilot\Billomat\Api\IncomingTagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\IncomingTag;
use Justpilot\Billomat\Model\IncomingTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(IncomingTagsApi::class)]
#[CoversClass(IncomingTagCreateOptions::class)]
#[CoversClass(IncomingTag::class)]
#[CoversClass(IncomingTagCloudEntry::class)]
final class IncomingTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsAndCloud(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            if (str_contains($url, 'incoming_id=42')) {
                $body = json_encode([
                    'incoming-tags' => [
                        'incoming-tag' => [['id' => 1, 'incoming_id' => 42, 'name' => 'Büro']],
                    ],
                ], JSON_THROW_ON_ERROR);
            } else {
                $body = json_encode([
                    'incoming-tags' => ['tag' => [['id' => 1, 'name' => 'Büro', 'count' => 8]]],
                ], JSON_THROW_ON_ERROR);
            }

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new IncomingTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $tags = $api->listByIncoming(42);
        self::assertCount(1, $tags);

        $cloud = $api->cloud();
        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(IncomingTagCloudEntry::class, $cloud);
    }

    #[Test]
    public function itCreatesAndDeletesTag(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            if ('POST' === $method) {
                return new MockResponse(
                    json_encode([
                        'incoming-tag' => ['id' => 99, 'incoming_id' => 42, 'name' => 'neu'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new IncomingTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $tag = $api->create(new IncomingTagCreateOptions(incomingId: 42, name: 'neu'));
        self::assertSame(99, $tag->id);
        self::assertTrue($api->delete(99));
    }
}
