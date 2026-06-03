<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InboxDocumentCreateOptions::class)]
final class InboxDocumentCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadHasFilenameMimeTypeAndContent(): void
    {
        $options = new InboxDocumentCreateOptions(
            filename: 'beleg.pdf',
            mimeType: 'application/pdf',
            base64file: 'JVBERi0xLg==',
        );

        self::assertSame(
            [
                'filename' => 'beleg.pdf',
                'mimetype' => 'application/pdf',
                'base64file' => 'JVBERi0xLg==',
            ],
            $options->toArray(),
        );
    }

    #[Test]
    public function itSerializesDocumentTypeAndMetadata(): void
    {
        $options = new InboxDocumentCreateOptions(
            filename: 'beleg.pdf',
            mimeType: 'application/pdf',
            base64file: 'JVBERi0xLg==',
        );
        $options->documentType = 'invoice';
        $options->metadata = ['vendor' => 'ACME', 'amount' => 99.0];

        $payload = $options->toArray();

        self::assertSame('invoice', $payload['document_type']);
        self::assertSame(['vendor' => 'ACME', 'amount' => 99.0], $payload['metadata']);
    }
}
