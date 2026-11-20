<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\CreditNoteTagCreateOptions;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\CreditNoteTag;
use Justpilot\Billomat\Model\CreditNoteTagCloudEntry;
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
#[UsesClass(CreditNotesApi::class)]
#[CoversClass(CreditNoteTagCreateOptions::class)]
#[CoversClass(CreditNoteTag::class)]
#[CoversClass(CreditNoteTagCloudEntry::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class CreditNoteTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsByCreditNoteAndLoadsCloud(): void
    {
        $captured = [];
        $count = 0;

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured, &$count): MockResponse {
            $captured[] = $url;
            ++$count;

            if (str_contains($url, 'credit_note_id=42')) {
                $body = json_encode([
                    'credit-note-tags' => [
                        'credit-note-tag' => [['id' => 1, 'credit_note_id' => 42, 'name' => 'wichtig']],
                    ],
                ], JSON_THROW_ON_ERROR);
            } else {
                $body = json_encode([
                    'credit-note-tags' => [
                        'tag' => [['id' => 1, 'name' => 'wichtig', 'count' => 4]],
                    ],
                ], JSON_THROW_ON_ERROR);
            }

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tags = $api->list(42);
        self::assertCount(1, $tags);
        self::assertSame('wichtig', $tags[0]->name);

        $cloud = $api->cloud();
        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(CreditNoteTagCloudEntry::class, $cloud);
        self::assertSame(4, $cloud[0]->count);
    }

    #[Test]
    public function itCreatesAndDeletes(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = CapturedRequest::of($method, $url);

            if ('POST' === $method) {
                return new MockResponse(
                    json_encode([
                        'credit-note-tag' => ['id' => 99, 'credit_note_id' => 42, 'name' => 'neu'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new CreditNotesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->tags;

        $tag = $api->create(42, new CreditNoteTagCreateOptions(name: 'neu'));
        self::assertSame(99, $tag->id);
        $api->delete(99);
    }
}
