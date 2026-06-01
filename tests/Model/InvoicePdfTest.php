<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\InvoicePdf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoicePdf::class)]
final class InvoicePdfTest extends TestCase
{
    #[Test]
    public function itHydratesAllFieldsFromArray(): void
    {
        $pdf = InvoicePdf::fromArray([
            'id' => 4882,
            'created' => '2009-09-02T12:04:15+02:00',
            'invoice_id' => 240,
            'filename' => 'invoice_123.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => 70137,
            'base64file' => base64_encode('PDF-binary'),
        ]);

        self::assertSame(4882, $pdf->id);
        self::assertSame(240, $pdf->invoiceId);
        self::assertInstanceOf(DateTimeImmutable::class, $pdf->created);
        self::assertSame('2009-09-02T12:04:15+02:00', $pdf->created->format('Y-m-d\TH:i:sP'));
        self::assertSame('invoice_123.pdf', $pdf->filename);
        self::assertSame('application/pdf', $pdf->mimeType);
        self::assertSame(70137, $pdf->fileSize);
    }

    #[Test]
    public function itDefaultsCreatedToNullWhenMissing(): void
    {
        $pdf = InvoicePdf::fromArray([
            'id' => 1,
            'invoice_id' => 2,
            'filename' => 'x.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => 0,
            'base64file' => '',
        ]);

        self::assertNull($pdf->created);
    }

    #[Test]
    public function itDefaultsCreatedToNullWhenEmptyString(): void
    {
        $pdf = InvoicePdf::fromArray([
            'created' => '',
            'id' => 1,
            'invoice_id' => 2,
            'filename' => 'x.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => 0,
            'base64file' => '',
        ]);

        self::assertNull($pdf->created);
    }

    #[Test]
    public function itDefaultsCreatedToNullOnInvalidDateString(): void
    {
        // defensiv: wenn Billomat ein kaputtes Datum schickt, soll created null sein
        $pdf = InvoicePdf::fromArray([
            'created' => 'kein-datum',
            'id' => 1,
            'invoice_id' => 2,
            'filename' => 'x.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => 0,
            'base64file' => '',
        ]);

        self::assertNull($pdf->created);
    }

    #[Test]
    public function itFallsBackToSensibleDefaultsForMissingFields(): void
    {
        $pdf = InvoicePdf::fromArray([]);

        self::assertSame(0, $pdf->id);
        self::assertSame(0, $pdf->invoiceId);
        self::assertSame('', $pdf->filename);
        self::assertSame('application/pdf', $pdf->mimeType);
        self::assertSame(0, $pdf->fileSize);
        self::assertSame('', $pdf->base64file);
        self::assertNull($pdf->created);
    }

    #[Test]
    public function getBinaryDecodesBase64Payload(): void
    {
        $original = "Binary\x00content\x01with\x02bytes";
        $pdf = InvoicePdf::fromArray([
            'id' => 1,
            'invoice_id' => 2,
            'filename' => 'x.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => \strlen($original),
            'base64file' => base64_encode($original),
        ]);

        self::assertSame($original, $pdf->getBinary());
    }

    #[Test]
    public function getBinaryReturnsEmptyStringForInvalidBase64(): void
    {
        $pdf = InvoicePdf::fromArray([
            'id' => 1,
            'invoice_id' => 2,
            'filename' => 'x.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => 0,
            'base64file' => '###not-valid-base64###',
        ]);

        self::assertSame('', $pdf->getBinary());
    }
}
