<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ArticlesApi;
use Justpilot\Billomat\Api\PropertiesApi;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Model\Enum\PropertyType;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(PropertiesApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[UsesClass(ArticlesApi::class)]
#[CoversClass(PropertyCreateOptions::class)]
#[CoversClass(ArticleProperty::class)]
#[CoversClass(PropertyType::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ArticlePropertiesApiTest extends TestCase
{
    #[Test]
    public function itListsArticleProperties(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'article-properties' => [
                    'article-property' => [
                        ['id' => 1, 'name' => 'Farbe', 'type' => 'TEXTFIELD', 'position' => 1],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->properties;

        $props = $api->list();
        self::assertCount(1, $props);
        self::assertSame('Farbe', $props[0]->name);
        self::assertSame(PropertyType::TEXTFIELD, $props[0]->type);
    }

    #[Test]
    public function itCreatesArticleProperty(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, '', $options);

            $body = json_encode([
                'article-property' => ['id' => 99, 'name' => 'Größe', 'type' => 'TEXTFIELD'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ArticlesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->properties;

        $opts = new PropertyCreateOptions(name: 'Größe', type: PropertyType::TEXTFIELD);

        $created = $api->create($opts);
        self::assertSame(99, $created->id);
    }
}
