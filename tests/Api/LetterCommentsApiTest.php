<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\CommentsApi;
use Justpilot\Billomat\Api\LetterCommentCreateOptions;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\LetterCommentActionKey;
use Justpilot\Billomat\Model\LetterComment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CommentsApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[UsesClass(LettersApi::class)]
#[CoversClass(LetterCommentCreateOptions::class)]
#[CoversClass(LetterComment::class)]
#[CoversClass(LetterCommentActionKey::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class LetterCommentsApiTest extends TestCase
{
    #[Test]
    public function itListsCommentsByLetter(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'letter-comments' => [
                    'letter-comment' => [
                        ['id' => 1, 'letter_id' => 42, 'comment' => 'Erstellt', 'actionkey' => 'CREATE'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $comments = $api->list(42, [LetterCommentActionKey::CREATE]);

        self::assertCount(1, $comments);
        self::assertSame(LetterCommentActionKey::CREATE, $comments[0]->actionkey);
        self::assertStringContainsString('letter_id=42', $captured['url']);
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
                        'letter-comment' => ['id' => 99, 'letter_id' => 42, 'comment' => 'Hallo'],
                    ], JSON_THROW_ON_ERROR),
                    ['http_code' => 201]
                );
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new LettersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $created = $api->create(new LetterCommentCreateOptions(letterId: 42, comment: 'Hallo'));

        self::assertSame(99, $created->id);
        $api->delete(99);
    }
}
