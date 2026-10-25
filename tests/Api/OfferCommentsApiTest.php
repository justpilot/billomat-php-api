<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\CommentsApi;
use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use Justpilot\Billomat\Model\OfferComment;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
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
#[UsesClass(OffersApi::class)]
#[CoversClass(OfferCommentCreateOptions::class)]
#[CoversClass(OfferComment::class)]
#[CoversClass(OfferCommentActionKey::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class OfferCommentsApiTest extends TestCase
{
    #[Test]
    public function itListsCommentsByOffer(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'offer-comments' => [
                    'offer-comment' => [
                        [
                            'id' => 1,
                            'offer_id' => 42,
                            'comment' => 'Erstellt',
                            'actionkey' => 'CREATE',
                            'created' => '2026-01-01T10:00:00+01:00',
                        ],
                        [
                            'id' => 2,
                            'offer_id' => 42,
                            'comment' => 'Manueller Kommentar',
                            'actionkey' => '',
                            'created' => '2026-01-02T10:00:00+01:00',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $comments = $api->list(42, [OfferCommentActionKey::CREATE, OfferCommentActionKey::COMMENT]);

        self::assertCount(2, $comments);
        self::assertSame(OfferCommentActionKey::CREATE, $comments[0]->actionkey);
        self::assertNull($comments[1]->actionkey);
        self::assertStringContainsString('offer_id=42', $captured->url);
        self::assertStringContainsString('actionkey=CREATE%2CCOMMENT', $captured->url);
    }

    #[Test]
    public function itCreatesComment(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'offer-comment' => [
                    'id' => 99,
                    'offer_id' => 42,
                    'comment' => 'Hallo',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $opts = new OfferCommentCreateOptions(comment: 'Hallo');
        $created = $api->create(42, $opts);

        self::assertSame(99, $created->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/offer-comments', $captured->url);

        self::assertSame(42, $captured->jsonAt('offer-comment', 'offer_id'));
        self::assertSame('Hallo', $captured->jsonAt('offer-comment', 'comment'));
    }

    #[Test]
    public function itDeletesComment(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->record($method, $url);

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new OffersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ))->comments;

        $api->delete(99);
        self::assertSame('DELETE', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/offer-comments/99', $captured->url);
    }
}
