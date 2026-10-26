<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\MailOptions;
use Justpilot\Billomat\Tests\Support\Payload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MailOptions::class)]
final class MailOptionsTest extends TestCase
{
    #[Test]
    public function emptyOptionsProduceEmptyPayload(): void
    {
        self::assertSame([], new MailOptions()->toArray());
    }

    #[Test]
    public function itConvertsBoolColorAndDuplexToOneZero(): void
    {
        $options = new MailOptions(color: true, duplex: false);

        $payload = $options->toArray();

        self::assertSame(1, $payload['color']);
        self::assertSame(0, $payload['duplex']);
    }

    #[Test]
    public function itPassesPaperWeightThroughUntouched(): void
    {
        $options = new MailOptions(paperWeight: 90);

        $payload = $options->toArray();

        self::assertSame(90, $payload['paper_weight']);
    }

    #[Test]
    public function itStripsNullFields(): void
    {
        $options = new MailOptions(color: true);

        $payload = $options->toArray();

        self::assertSame(['color' => 1], $payload);
    }

    #[Test]
    public function itWrapsAttachmentsInAttachmentEnvelope(): void
    {
        $options = new MailOptions(
            attachments: [
                ['filename' => 'agb.pdf', 'mimetype' => 'application/pdf', 'base64file' => 'QUJD'],
                ['filename' => 'flyer.pdf', 'mimetype' => 'application/pdf', 'base64file' => 'WFla'],
            ],
        );

        $payload = $options->toArray();

        self::assertArrayHasKey('attachments', $payload);
        self::assertCount(2, Payload::arrayAt($payload, 'attachments', 'attachment'));
        self::assertSame('agb.pdf', Payload::at($payload, 'attachments', 'attachment', 0, 'filename'));
        self::assertSame('application/pdf', Payload::at($payload, 'attachments', 'attachment', 0, 'mimetype'));
        self::assertSame('QUJD', Payload::at($payload, 'attachments', 'attachment', 0, 'base64file'));
    }

    #[Test]
    public function itOmitsAttachmentsKeyWhenEmpty(): void
    {
        $options = new MailOptions();

        self::assertArrayNotHasKey('attachments', $options->toArray());
    }
}
