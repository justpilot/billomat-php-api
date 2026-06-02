<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Api\InboxDocumentsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\InboxDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(InboxDocumentsApi::class)]
#[CoversClass(InboxDocumentCreateOptions::class)]
#[CoversClass(InboxDocument::class)]
final class InboxDocumentsApiTest extends TestCase
{
    #[Test]
    public function itListsInboxDocuments(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'inbox-documents' => [
                    'inbox-document' => [
                        ['id' => 1, 'filename' => 'rechnung.pdf', 'mimetype' => 'application/pdf', 'filesize' => 1234],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new InboxDocumentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $docs = $api->list();
        self::assertCount(1, $docs);
        self::assertSame('rechnung.pdf', $docs[0]->filename);
    }

    #[Test]
    public function itGetsAndDecodesDocument(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'inbox-document' => [
                    'id' => 1,
                    'filename' => 'rechnung.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 1234,
                    'base64file' => base64_encode('%PDF-INHALT%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new InboxDocumentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $doc = $api->get(1);
        self::assertInstanceOf(InboxDocument::class, $doc);
        self::assertStringStartsWith('%PDF-INHALT%', $doc->getBinary());
    }

    #[Test]
    public function itUploadsDocument(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'inbox-document' => [
                    'id' => 777,
                    'filename' => 'beleg.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 1024,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new InboxDocumentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new InboxDocumentCreateOptions(
            filename: 'beleg.pdf',
            mimeType: 'application/pdf',
            base64file: base64_encode('PDF'),
        );

        $created = $api->create($opts);
        self::assertSame(777, $created->id);
        self::assertSame('POST', $captured['method']);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame('beleg.pdf', $payload['inbox-document']['filename']);
        self::assertSame('application/pdf', $payload['inbox-document']['mimetype']);
    }

    #[Test]
    public function itDeletesDocument(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new InboxDocumentsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(99));
        self::assertSame('DELETE', $captured['method']);
    }
}
