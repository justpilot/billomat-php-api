<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ArticlePropertiesApi;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Model\Enum\PropertyType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ArticlePropertiesApi::class)]
#[CoversClass(PropertyCreateOptions::class)]
#[CoversClass(ArticleProperty::class)]
#[CoversClass(PropertyType::class)]
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

        $api = new ArticlePropertiesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $props = $api->list();
        self::assertCount(1, $props);
        self::assertSame('Farbe', $props[0]->name);
        self::assertSame(PropertyType::TEXTFIELD, $props[0]->type);
    }

    #[Test]
    public function itCreatesArticleProperty(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'options' => $options];

            $body = json_encode([
                'article-property' => ['id' => 99, 'name' => 'Größe', 'type' => 'TEXTFIELD'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ArticlePropertiesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new PropertyCreateOptions(name: 'Größe');
        $opts->type = PropertyType::TEXTFIELD;

        $created = $api->create($opts);
        self::assertSame(99, $created->id);
    }
}
