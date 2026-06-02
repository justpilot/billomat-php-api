<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteTagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\DeliveryNoteTag;
use Justpilot\Billomat\Model\DeliveryNoteTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(DeliveryNoteTagsApi::class)]
#[CoversClass(DeliveryNoteTagCreateOptions::class)]
#[CoversClass(DeliveryNoteTag::class)]
#[CoversClass(DeliveryNoteTagCloudEntry::class)]
final class DeliveryNoteTagsApiTest extends TestCase
{
    #[Test]
    public function itListsTagsByDeliveryNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'delivery-note-tags' => [
                    'delivery-note-tag' => [
                        ['id' => 1, 'delivery_note_id' => 42, 'name' => 'eilig'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNoteTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $tags = $api->listByDeliveryNote(42);

        self::assertCount(1, $tags);
        self::assertSame('eilig', $tags[0]->name);
        self::assertStringContainsString('delivery_note_id=42', $captured['url']);
    }

    #[Test]
    public function itLoadsTagCloud(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'delivery-note-tags' => [
                    'tag' => [['id' => 1, 'name' => 'eilig', 'count' => 3]],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNoteTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $cloud = $api->cloud();

        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(DeliveryNoteTagCloudEntry::class, $cloud);
    }

    #[Test]
    public function itCreatesAndDeletesTag(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            if ('POST' === $method) {
                $body = json_encode([
                    'delivery-note-tag' => ['id' => 99, 'delivery_note_id' => 42, 'name' => 'neu'],
                ], JSON_THROW_ON_ERROR);

                return new MockResponse($body, ['http_code' => 201]);
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new DeliveryNoteTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $tag = $api->create(new DeliveryNoteTagCreateOptions(deliveryNoteId: 42, name: 'neu'));
        self::assertSame(99, $tag->id);
        self::assertTrue($api->delete(99));
    }
}
