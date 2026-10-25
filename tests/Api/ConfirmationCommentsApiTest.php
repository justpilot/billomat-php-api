<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\CommentsApi;
use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
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
#[UsesClass(ConfirmationsApi::class)]
#[CoversClass(ConfirmationCommentCreateOptions::class)]
#[CoversClass(ConfirmationComment::class)]
#[CoversClass(ConfirmationCommentActionKey::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class ConfirmationCommentsApiTest extends TestCase
{
    #[Test]
    public function itListsCommentsByConfirmation(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'confirmation-comments' => [
                    'confirmation-comment' => [
                        ['id' => 1, 'confirmation_id' => 42, 'comment' => 'Erstellt', 'actionkey' => 'CREATE'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $comments = $api->list(42, [ConfirmationCommentActionKey::CREATE]);

        self::assertCount(1, $comments);
        self::assertSame(ConfirmationCommentActionKey::CREATE, $comments[0]->actionkey);
        self::assertStringContainsString('confirmation_id=42', $captured['url']);
    }

    #[Test]
    public function itCreatesComment(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'confirmation-comment' => ['id' => 99, 'confirmation_id' => 42, 'comment' => 'Hallo'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $created = $api->create(new ConfirmationCommentCreateOptions(confirmationId: 42, comment: 'Hallo'));

        self::assertSame(99, $created->id);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/confirmation-comments', $captured['url']);
    }

    #[Test]
    public function itDeletesComment(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ConfirmationsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $api->delete(99);
        self::assertSame('DELETE', $captured['method']);
    }
}
