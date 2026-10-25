<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\OfferTag;
use Justpilot\Billomat\Model\OfferTagCloudEntry;
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
#[UsesClass(OffersApi::class)]
#[CoversClass(OfferTagCreateOptions::class)]
#[CoversClass(OfferTag::class)]
#[CoversClass(OfferTagCloudEntry::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class OfferTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsByOffer(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

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

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tags = $api->list(42);

        self::assertCount(2, $tags);
        self::assertSame('wichtig', $tags[0]->name);
        self::assertStringContainsString('offer_id=42', $captured->url);
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

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $cloud = $api->cloud();

        self::assertCount(2, $cloud);
        self::assertContainsOnlyInstancesOf(OfferTagCloudEntry::class, $cloud);
        self::assertSame('wichtig', $cloud[0]->name);
        self::assertSame(5, $cloud[0]->count);
    }

    #[Test]
    public function itCreatesTag(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'offer-tag' => ['id' => 99, 'offer_id' => 42, 'name' => 'neu'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $opts = new OfferTagCreateOptions(name: 'neu');
        $tag = $api->create(42, $opts);

        self::assertSame(99, $tag->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/offer-tags', $captured->url);
    }

    #[Test]
    public function itDeletesTag(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $api->delete(99);
        self::assertSame('DELETE', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/offer-tags/99', $captured->url);
    }
}
