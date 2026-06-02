<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\IncomingCommentCreateOptions;
use Justpilot\Billomat\Api\IncomingCommentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\IncomingCommentActionKey;
use Justpilot\Billomat\Model\IncomingComment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(IncomingCommentsApi::class)]
#[CoversClass(IncomingCommentCreateOptions::class)]
#[CoversClass(IncomingComment::class)]
#[CoversClass(IncomingCommentActionKey::class)]
final class IncomingCommentsApiTest extends TestCase
{
    #[Test]
    public function itListsCreatesAndDeletes(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            if ('GET' === $method) {
                return new MockResponse(
                    json_encode([
                        'incoming-comments' => [
                            'incoming-comment' => [
                                ['id' => 1, 'incoming_id' => 42, 'comment' => 'Erstellt', 'actionkey' => 'CREATE'],
                            ],
                        ],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 200]
                );
            }

            if ('POST' === $method) {
                return new MockResponse(
                    json_encode([
                        'incoming-comment' => ['id' => 99, 'incoming_id' => 42, 'comment' => 'Hallo'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new IncomingCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $comments = $api->listByIncoming(42, [IncomingCommentActionKey::CREATE]);
        self::assertCount(1, $comments);
        self::assertSame(IncomingCommentActionKey::CREATE, $comments[0]->actionkey);

        $created = $api->create(new IncomingCommentCreateOptions(incomingId: 42, comment: 'Hallo'));
        self::assertSame(99, $created->id);

        self::assertTrue($api->delete(99));
    }
}
