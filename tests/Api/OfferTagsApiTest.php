<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\OfferTagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\OfferTag;
use Justpilot\Billomat\Model\OfferTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(OfferTagsApi::class)]
#[CoversClass(OfferTagCreateOptions::class)]
#[CoversClass(OfferTag::class)]
#[CoversClass(OfferTagCloudEntry::class)]
final class OfferTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsByOffer(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'offer-tags' => [
                    'offer-tag' => [
                        ['id' => 1, 'offer_id' => 42, 'name' => 'wichtig'],
                        ['id' => 2, 'offer_id' => 42, 'name' => 'großkunde'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OfferTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $tags = $api->listByOffer(42);

        self::assertCount(2, $tags);
        self::assertSame('wichtig', $tags[0]->name);
        self::assertStringContainsString('offer_id=42', $captured['url']);
    }

    #[Test]
    public function itLoadsTagCloud(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'offer-tags' => [
                    'tag' => [
                        ['id' => 1, 'name' => 'wichtig', 'count' => 5],
                        ['id' => 2, 'name' => 'eilig', 'count' => 2],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OfferTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $cloud = $api->cloud();

        self::assertCount(2, $cloud);
        self::assertContainsOnlyInstancesOf(OfferTagCloudEntry::class, $cloud);
        self::assertSame('wichtig', $cloud[0]->name);
        self::assertSame(5, $cloud[0]->count);
    }

    #[Test]
    public function itCreatesTag(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'offer-tag' => ['id' => 99, 'offer_id' => 42, 'name' => 'neu'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new OfferTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new OfferTagCreateOptions(offerId: 42, name: 'neu');
        $tag = $api->create($opts);

        self::assertSame(99, $tag->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offer-tags', $captured['url']);
    }

    #[Test]
    public function itDeletesTag(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new OfferTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(99));
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/offer-tags/99', $captured['url']);
    }
}
