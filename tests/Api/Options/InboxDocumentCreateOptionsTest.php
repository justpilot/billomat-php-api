<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use InvalidArgumentException;
use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Model\Enum\InboxDocumentMetadataKey;
use Justpilot\Billomat\Model\Enum\InboxDocumentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InboxDocumentCreateOptions::class)]
#[UsesClass(InboxDocumentMetadataKey::class)]
#[UsesClass(InboxDocumentType::class)]
final class InboxDocumentCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadHasFilenameMimeTypeAndContent(): void
    {
        $options = new InboxDocumentCreateOptions(
            filename: 'beleg.pdf',
            mimeType: 'application/pdf',
            base64File: 'JVBERi0xLg==',
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
    public function itSerializesDocumentTypeAndMetadataAsKeyValuePairs(): void
    {
        $options = new InboxDocumentCreateOptions(
            filename: 'beleg.pdf',
            mimeType: 'application/pdf',
            base64File: 'JVBERi0xLg==',
            documentType: InboxDocumentType::INVOICE,
            metadata: [
                InboxDocumentMetadataKey::SENDER_NAME->value => 'ACME GmbH',
                InboxDocumentMetadataKey::TOTAL_GROSS->value => '119.00',
            ],
        );

        $payload = $options->toArray();

        self::assertSame('INVOICE', $payload['document_type']);
        // Format im Sandbox gemessen: ein flaches Objekt lehnt Billomat mit „Unknown meta data field“ ab.
        self::assertSame(
            ['data' => [
                ['key' => 'SENDER_NAME', 'value' => 'ACME GmbH'],
                ['key' => 'TOTAL_GROSS', 'value' => '119.00'],
            ]],
            $payload['metadata'],
        );
    }

    #[Test]
    public function emptyMetadataIsSentAsEmptyList(): void
    {
        $options = new InboxDocumentCreateOptions(filename: 'beleg.pdf', mimeType: 'application/pdf', base64File: 'JVBERi0xLg==', metadata: []);

        self::assertSame(['data' => []], $options->toArray()['metadata']);
    }

    #[Test]
    public function itRejectsUnknownMetadataKeys(): void
    {
        $options = new InboxDocumentCreateOptions(filename: 'beleg.pdf', mimeType: 'application/pdf', base64File: 'JVBERi0xLg==')
            ->withMetadata(['SENDER_NAME' => 'ACME', 'vendor' => 'ACME']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Unknown inbox document metadata key "vendor"');

        $options->toArray();
    }
}
