<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;
use Justpilot\Billomat\Api\ArticlesApi;
use Justpilot\Billomat\Api\PropertyValuesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(PropertyValuesApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[UsesClass(ArticlesApi::class)]
#[CoversClass(ArticlePropertyValueCreateOptions::class)]
#[CoversClass(ArticlePropertyValue::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ArticlePropertyValuesApiTest extends TestCase
{
    #[Test]
    public function itListsValuesByArticle(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'article-property-values' => [
                    'article-property-value' => [
                        ['id' => 1, 'article_id' => 42, 'article_property_id' => 7, 'name' => 'Farbe', 'value' => 'rot'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->propertyValues;

        $values = $api->list(filters: ['article_id' => 42]);

        self::assertCount(1, $values);
        self::assertSame('Farbe', $values[0]->name);
        self::assertSame('rot', $values[0]->value);
        self::assertStringContainsString('article_id=42', $captured['url']);
    }

    #[Test]
    public function itCreatesValue(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'article-property-value' => ['id' => 99, 'article_id' => 42, 'article_property_id' => 7, 'value' => 'blau'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->propertyValues;

        $value = $api->create(new ArticlePropertyValueCreateOptions(
            articleId: 42,
            articlePropertyId: 7,
            value: 'blau',
        ));

        self::assertSame(99, $value->id);
        self::assertSame('POST', $captured['method']);
    }
}
