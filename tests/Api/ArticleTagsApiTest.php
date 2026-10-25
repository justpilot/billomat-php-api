<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ArticlesApi;
use Justpilot\Billomat\Api\ArticleTagCreateOptions;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ArticleTag;
use Justpilot\Billomat\Model\ArticleTagCloudEntry;
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
#[UsesClass(ArticlesApi::class)]
#[CoversClass(ArticleTagCreateOptions::class)]
#[CoversClass(ArticleTag::class)]
#[CoversClass(ArticleTagCloudEntry::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ArticleTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsByArticle(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'article-tags' => [
                    'article-tag' => [['id' => 1, 'article_id' => 42, 'name' => 'bestseller']],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tags = $api->list(42);
        self::assertCount(1, $tags);
        self::assertSame('bestseller', $tags[0]->name);
        self::assertStringContainsString('article_id=42', $captured['url']);
    }

    #[Test]
    public function itLoadsCloud(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'article-tags' => ['tag' => [['id' => 1, 'name' => 'bestseller', 'count' => 7]]],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $cloud = $api->cloud();
        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(ArticleTagCloudEntry::class, $cloud);
        self::assertSame(7, $cloud[0]->count);
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
                        'article-tag' => ['id' => 99, 'article_id' => 42, 'name' => 'neu'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tag = $api->create(new ArticleTagCreateOptions(articleId: 42, name: 'neu'));
        self::assertSame(99, $tag->id);
        $api->delete(99);
    }
}
