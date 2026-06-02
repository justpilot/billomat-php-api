<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\CreditNoteCommentCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCommentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CreditNoteCommentsApi::class)]
#[CoversClass(CreditNoteCommentCreateOptions::class)]
#[CoversClass(CreditNoteComment::class)]
#[CoversClass(CreditNoteCommentActionKey::class)]
final class CreditNoteCommentsApiTest extends TestCase
{
    #[Test]
    public function itListsCommentsByCreditNote(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'credit-note-comments' => [
                    'credit-note-comment' => [
                        ['id' => 1, 'credit_note_id' => 42, 'comment' => 'Erstellt', 'actionkey' => 'CREATE'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CreditNoteCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $comments = $api->listByCreditNote(42, [CreditNoteCommentActionKey::CREATE]);

        self::assertCount(1, $comments);
        self::assertSame(CreditNoteCommentActionKey::CREATE, $comments[0]->actionkey);
        self::assertStringContainsString('credit_note_id=42', $captured['url']);
    }

    #[Test]
    public function itCreatesAndDeletes(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];

            if ('POST' === $method) {
                return new MockResponse(
                    json_encode([
                        'credit-note-comment' => ['id' => 99, 'credit_note_id' => 42, 'comment' => 'Hallo'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new CreditNoteCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $created = $api->create(new CreditNoteCommentCreateOptions(creditNoteId: 42, comment: 'Hallo'));

        self::assertSame(99, $created->id);
        self::assertTrue($api->delete(99));
    }
}
