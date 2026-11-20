<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\InboxDocuments;

use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Model\Enum\InboxDocumentMetadataKey;
use Justpilot\Billomat\Model\Enum\InboxDocumentType;
use Justpilot\Billomat\Model\InboxDocument;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class InboxDocumentsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    /**
     * Minimaler, gültiger PDF-Header als Test-Payload.
     */
    private const string MINIMAL_PDF = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF";

    #[Group('integration')]
    #[Test]
    public function canListInboxDocumentsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $docs = $billomat->inboxDocuments->list(['per_page' => 5]);

        self::assertIsArray($docs);
        self::assertContainsOnlyInstancesOf(InboxDocument::class, $docs);
    }

    #[Group('integration')]
    #[Test]
    public function canUploadAndDeleteInboxDocumentInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $opts = new InboxDocumentCreateOptions(
            filename: 'it-test-'.date('His').'.pdf',
            mimeType: 'application/pdf',
            base64File: base64_encode(self::MINIMAL_PDF),
            documentType: InboxDocumentType::INVOICE,
            metadata: [InboxDocumentMetadataKey::SENDER_CITY->value => 'Siegen', InboxDocumentMetadataKey::WWW->value => 'example.com'],
        );

        $doc = $billomat->inboxDocuments->create($opts);
        $loaded = $billomat->inboxDocuments->getOrFail($doc->id);

        self::assertSame(InboxDocumentType::INVOICE, $loaded->documentType);
        self::assertSame(['SENDER_CITY' => 'Siegen', 'WWW' => 'example.com'], $loaded->metadata);

        $billomat->inboxDocuments->delete($doc->id);
        self::assertNull($billomat->inboxDocuments->get($doc->id));
    }
}
