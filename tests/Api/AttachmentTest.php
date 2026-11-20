<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use InvalidArgumentException;
use Justpilot\Billomat\Api\Attachment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attachment::class)]
final class AttachmentTest extends TestCase
{
    #[Test]
    public function serializesToBillomatsWireNames(): void
    {
        self::assertSame(
            ['filename' => 'agb.pdf', 'mimetype' => 'application/pdf', 'base64file' => 'QUJD'],
            new Attachment('agb.pdf', 'application/pdf', 'QUJD')->toArray(),
        );
    }

    #[Test]
    public function fromBinaryEncodesTheContent(): void
    {
        $attachment = Attachment::fromBinary('bild.png', "\x89PNG", 'image/png');

        self::assertSame('bild.png', $attachment->filename);
        self::assertSame('image/png', $attachment->mimeType);
        self::assertSame(base64_encode("\x89PNG"), $attachment->base64File);
        self::assertSame('application/pdf', Attachment::fromBinary('x.pdf', 'x')->mimeType);
    }

    #[Test]
    public function fromFileReadsTheFileAndTakesItsName(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'att');
        self::assertIsString($path);
        file_put_contents($path, '%PDF-1.4');

        try {
            $attachment = Attachment::fromFile($path);
            self::assertSame(basename($path), $attachment->filename);
            self::assertSame('application/pdf', $attachment->mimeType);
            self::assertSame(base64_encode('%PDF-1.4'), $attachment->base64File);

            $renamed = Attachment::fromFile($path, 'agb.txt', 'text/plain');
            self::assertSame('agb.txt', $renamed->filename);
            self::assertSame('text/plain', $renamed->mimeType);
        } finally {
            unlink($path);
        }
    }

    #[Test]
    public function fromFileRejectsMissingFiles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Attachment file "/nicht/vorhanden.pdf" is not readable.');

        Attachment::fromFile('/nicht/vorhanden.pdf');
    }

    #[Test]
    public function fromFileRejectsDirectories(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Attachment::fromFile(sys_get_temp_dir());
    }
}
