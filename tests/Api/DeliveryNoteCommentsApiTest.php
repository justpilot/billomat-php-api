<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCommentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\DeliveryNoteComment;
use Justpilot\Billomat\Model\Enum\DeliveryNoteCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(DeliveryNoteCommentsApi::class)]
#[CoversClass(DeliveryNoteCommentCreateOptions::class)]
#[CoversClass(DeliveryNoteComment::class)]
#[CoversClass(DeliveryNoteCommentActionKey::class)]
final class DeliveryNoteCommentsApiTest extends TestCase
{
    #[Test]
    public function itListsCommentsByDeliveryNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'delivery-note-comments' => [
                    'delivery-note-comment' => [
                        ['id' => 1, 'delivery_note_id' => 42, 'comment' => 'Erstellt', 'actionkey' => 'CREATE'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new DeliveryNoteCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $comments = $api->listByDeliveryNote(42, [DeliveryNoteCommentActionKey::CREATE]);

        self::assertCount(1, $comments);
        self::assertSame(DeliveryNoteCommentActionKey::CREATE, $comments[0]->actionkey);
        self::assertStringContainsString('delivery_note_id=42', $captured['url']);
    }

    #[Test]
    public function itCreatesAndDeletesComment(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            if ('POST' === $method) {
                $body = json_encode([
                    'delivery-note-comment' => ['id' => 99, 'delivery_note_id' => 42, 'comment' => 'Hallo'],
                ], JSON_THROW_ON_ERROR);

                return new MockResponse($body, ['http_code' => 201]);
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new DeliveryNoteCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $created = $api->create(new DeliveryNoteCommentCreateOptions(deliveryNoteId: 42, comment: 'Hallo'));

        self::assertSame(99, $created->id);
        self::assertTrue($api->delete(99));

        self::assertSame('POST', $captured[0]['method']);
        self::assertSame('DELETE', $captured[1]['method']);
    }
}
