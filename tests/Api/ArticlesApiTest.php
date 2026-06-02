<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Api\ArticlesApi;
use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Article;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ArticlesApi::class)]
#[CoversClass(ArticleCreateOptions::class)]
#[CoversClass(ArticleUpdateOptions::class)]
#[CoversClass(Article::class)]
final class ArticlesApiTest extends TestCase
{
    #[Test]
    public function itListsArticlesAndPassesFilters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'articles' => [
                    'article' => [
                        ['id' => 1, 'title' => 'Beratung', 'sales_price' => 100.0, 'unit' => 'Stunde'],
                        ['id' => 2, 'title' => 'Hardware', 'sales_price' => 50.0],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $articles = $api->list(['title' => 'Beratung']);

        self::assertCount(2, $articles);
        self::assertContainsOnlyInstancesOf(Article::class, $articles);
        self::assertSame('Beratung', $articles[0]->title);
        self::assertSame(100.0, $articles[0]->salesPrice);
        self::assertStringContainsString('title=Beratung', $captured['url']);
    }

    #[Test]
    public function itGetsSingleArticle(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'article' => [
                    'id' => 1234,
                    'title' => 'Test',
                    'article_number' => 'A-2026-001',
                    'sales_price' => 99.99,
                    'currency_code' => 'EUR',
                    'tax_id' => 1,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $article = $api->get(1234);

        self::assertInstanceOf(Article::class, $article);
        self::assertSame(1234, $article->id);
        self::assertSame('A-2026-001', $article->articleNumber);
        self::assertSame(99.99, $article->salesPrice);
        self::assertSame(1, $article->taxId);
    }

    #[Test]
    public function itReturnsNullWhenNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $api = new ArticlesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertNull($api->get(999999));
    }

    #[Test]
    public function itCreatesArticleViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'article' => ['id' => 777, 'title' => 'Neu', 'sales_price' => 25.0],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ArticleCreateOptions(title: 'Neu');
        $opts->salesPrice = 25.0;
        $opts->currencyCode = 'EUR';
        $opts->unit = 'Stück';

        $created = $api->create($opts);

        self::assertSame(777, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/articles', $captured['url']);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame('Neu', $payload['article']['title']);
        self::assertSame(25.0, $payload['article']['sales_price']);
        self::assertSame('Stück', $payload['article']['unit']);
    }

    #[Test]
    public function itUpdatesArticleViaPut(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'article' => ['id' => 777, 'title' => 'Geändert', 'sales_price' => 30.0],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ArticleUpdateOptions();
        $opts->salesPrice = 30.0;

        $updated = $api->update(777, $opts);

        self::assertSame(30.0, $updated->salesPrice);
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/articles/777', $captured['url']);
    }

    #[Test]
    public function itDeletesArticle(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(777));
        self::assertSame('DELETE', $captured['method']);
    }
}
