<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Api\InvoiceCommentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\InvoiceComment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(InvoiceCommentsApi::class)]
#[CoversClass(InvoiceComment::class)]
#[CoversClass(InvoiceCommentCreateOptions::class)]
final class InvoiceCommentsApiTest extends TestCase
{
    #[Test]
    public function itListsCommentsByInvoiceIdAndPassesActionkeyFilter(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice-comments' => [
                    'invoice-comment' => [
                        ['id' => 1, 'invoice_id' => 100, 'comment' => 'A', 'actionkey' => 'CREATE'],
                        ['id' => 2, 'invoice_id' => 100, 'comment' => 'B', 'actionkey' => 'COMPLETE'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceCommentsApi($http);

        $comments = $api->listByInvoice(100, [InvoiceCommentActionKey::CREATE, InvoiceCommentActionKey::COMPLETE]);

        self::assertCount(2, $comments);
        self::assertContainsOnlyInstancesOf(InvoiceComment::class, $comments);
        self::assertSame(InvoiceCommentActionKey::CREATE, $comments[0]->actionkey);

        $parts = parse_url($captured['url']);
        self::assertSame('/api/invoice-comments', $parts['path'] ?? null);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('100', $query['invoice_id'] ?? null);
        self::assertSame('CREATE,COMPLETE', $query['actionkey'] ?? null);
    }

    #[Test]
    public function listNormalizesSingleEntryWrappedAsAssocArray(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'invoice-comments' => [
                    'invoice-comment' => [
                        'id' => 7,
                        'invoice_id' => 5,
                        'comment' => 'Einzelfall',
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceCommentsApi($http);

        $comments = $api->listByInvoice(5);

        self::assertCount(1, $comments);
        self::assertSame(7, $comments[0]->id);
    }

    #[Test]
    public function getReturnsNullOnNotFound(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceCommentsApi($http);

        self::assertNull($api->get(999));
    }

    #[Test]
    public function createPostsCommentAndHydratesResponse(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'invoice-comment' => [
                    'id' => 99,
                    'invoice_id' => 100,
                    'comment' => 'Anruf vom Kunden.',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceCommentsApi($http);

        $comment = $api->create(new InvoiceCommentCreateOptions(invoiceId: 100, comment: 'Anruf vom Kunden.'));

        self::assertSame(99, $comment->id);
        self::assertSame(100, $comment->invoiceId);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/invoice-comments', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertSame(100, $payload['invoice-comment']['invoice_id']);
        self::assertSame('Anruf vom Kunden.', $payload['invoice-comment']['comment']);
    }

    #[Test]
    public function deleteSendsDeleteRequest(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoiceCommentsApi($http);

        self::assertTrue($api->delete(42));
        self::assertSame('DELETE', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/invoice-comments/42', $captured['url']);
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return array<string,mixed>|null
     */
    private function extractJsonPayload(array $options): ?array
    {
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body']) && '' !== $options['body']) {
            $decoded = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);

            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        return \is_array($payload) ? $payload : null;
    }
}
