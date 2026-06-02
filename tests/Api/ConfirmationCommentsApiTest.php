<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCommentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\ConfirmationComment;
use Justpilot\Billomat\Model\Enum\ConfirmationCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ConfirmationCommentsApi::class)]
#[CoversClass(ConfirmationCommentCreateOptions::class)]
#[CoversClass(ConfirmationComment::class)]
#[CoversClass(ConfirmationCommentActionKey::class)]
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

        $api = new ConfirmationCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $comments = $api->listByConfirmation(42, [ConfirmationCommentActionKey::CREATE]);

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

        $api = new ConfirmationCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

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

        $api = new ConfirmationCommentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(99));
        self::assertSame('DELETE', $captured['method']);
    }
}
