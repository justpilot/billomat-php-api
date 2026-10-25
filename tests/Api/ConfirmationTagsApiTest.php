<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\ConfirmationTagCreateOptions;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ConfirmationTag;
use Justpilot\Billomat\Model\ConfirmationTagCloudEntry;
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
#[UsesClass(ConfirmationsApi::class)]
#[CoversClass(ConfirmationTagCreateOptions::class)]
#[CoversClass(ConfirmationTag::class)]
#[CoversClass(ConfirmationTagCloudEntry::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ConfirmationTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsByConfirmation(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'confirmation-tags' => [
                    'confirmation-tag' => [
                        ['id' => 1, 'confirmation_id' => 42, 'name' => 'wichtig'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tags = $api->list(42);

        self::assertCount(1, $tags);
        self::assertSame('wichtig', $tags[0]->name);
        self::assertStringContainsString('confirmation_id=42', $captured->url);
    }

    #[Test]
    public function itLoadsTagCloud(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'confirmation-tags' => [
                    'tag' => [
                        ['id' => 1, 'name' => 'eilig', 'count' => 3],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $cloud = $api->cloud();

        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(ConfirmationTagCloudEntry::class, $cloud);
        self::assertSame('eilig', $cloud[0]->name);
        self::assertSame(3, $cloud[0]->count);
    }

    #[Test]
    public function itCreatesTag(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            $body = json_encode([
                'confirmation-tag' => ['id' => 99, 'confirmation_id' => 42, 'name' => 'neu'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tag = $api->create(new ConfirmationTagCreateOptions(confirmationId: 42, name: 'neu'));

        self::assertSame(99, $tag->id);
        self::assertSame('POST', $captured->method);
    }

    #[Test]
    public function itDeletesTag(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $api->delete(99);
        self::assertSame('DELETE', $captured->method);
    }
}
