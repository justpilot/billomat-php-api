<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\InvoiceEmailOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceEmailOptions::class)]
final class InvoiceEmailOptionsTest extends TestCase
{
    #[Test]
    public function emptyOptionsProduceEmptyEnvelope(): void
    {
        $options = new InvoiceEmailOptions();

        self::assertSame([], $options->toArray());
    }

    #[Test]
    public function itWrapsRecipientsInToCcBccBlocks(): void
    {
        $options = new InvoiceEmailOptions();
        $options->to = ['kunde@example.com'];
        $options->cc = ['cc1@example.com', 'cc2@example.com'];
        $options->bcc = ['archive@example.com'];

        $payload = $options->toArray();

        self::assertSame(
            [
                'to' => ['kunde@example.com'],
                'cc' => ['cc1@example.com', 'cc2@example.com'],
                'bcc' => ['archive@example.com'],
            ],
            $payload['recipients']
        );
    }

    #[Test]
    public function itOmitsEmptyRecipientGroups(): void
    {
        $options = new InvoiceEmailOptions();
        $options->to = ['kunde@example.com'];

        $payload = $options->toArray();

        self::assertArrayHasKey('to', $payload['recipients']);
        self::assertArrayNotHasKey('cc', $payload['recipients']);
        self::assertArrayNotHasKey('bcc', $payload['recipients']);
    }

    #[Test]
    public function itOmitsRecipientsWhenAllGroupsAreEmpty(): void
    {
        $options = new InvoiceEmailOptions();
        $options->from = 'info@example.com';
        $options->subject = 'Test';

        $payload = $options->toArray();

        self::assertArrayNotHasKey('recipients', $payload);
        self::assertSame('info@example.com', $payload['from']);
        self::assertSame('Test', $payload['subject']);
    }

    #[Test]
    public function itWrapsAttachmentsInAttachmentEnvelope(): void
    {
        $options = new InvoiceEmailOptions();
        $options->attachments = [
            ['filename' => 'agb.pdf', 'mimetype' => 'application/pdf', 'base64file' => 'QUJD'],
            ['filename' => 'flyer.png', 'mimetype' => 'image/png', 'base64file' => 'WFla'],
        ];

        $payload = $options->toArray();

        self::assertArrayHasKey('attachments', $payload);
        self::assertCount(2, $payload['attachments']['attachment']);
        self::assertSame('agb.pdf', $payload['attachments']['attachment'][0]['filename']);
        self::assertSame('application/pdf', $payload['attachments']['attachment'][0]['mimetype']);
        self::assertSame('QUJD', $payload['attachments']['attachment'][0]['base64file']);
    }

    #[Test]
    public function itOmitsAttachmentsKeyWhenEmpty(): void
    {
        $options = new InvoiceEmailOptions();

        self::assertArrayNotHasKey('attachments', $options->toArray());
    }

    #[Test]
    public function itPassesScalarFieldsThrough(): void
    {
        $options = new InvoiceEmailOptions();
        $options->from = 'info@example.com';
        $options->subject = 'Ihre Rechnung';
        $options->body = 'Anbei die Rechnung.';
        $options->filename = 'rechnung-001';

        $payload = $options->toArray();

        self::assertSame('info@example.com', $payload['from']);
        self::assertSame('Ihre Rechnung', $payload['subject']);
        self::assertSame('Anbei die Rechnung.', $payload['body']);
        self::assertSame('rechnung-001', $payload['filename']);
    }

    #[Test]
    public function itPassesEmailTemplateIdThrough(): void
    {
        $options = new InvoiceEmailOptions();
        $options->emailTemplateId = 42;

        $payload = $options->toArray();

        self::assertSame(42, $payload['email_template_id']);
    }

    #[Test]
    public function itOmitsEmailTemplateIdWhenNull(): void
    {
        $options = new InvoiceEmailOptions();
        $options->subject = 'Test';

        self::assertArrayNotHasKey('email_template_id', $options->toArray());
    }
}
