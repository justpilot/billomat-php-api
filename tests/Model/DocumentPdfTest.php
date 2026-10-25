<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ConfirmationPdf;
use Justpilot\Billomat\Model\CreditNotePdf;
use Justpilot\Billomat\Model\DeliveryNotePdf;
use Justpilot\Billomat\Model\InvoicePdf;
use Justpilot\Billomat\Model\LetterPdf;
use Justpilot\Billomat\Model\OfferPdf;
use Justpilot\Billomat\Model\ReminderPdf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use const DATE_ATOM;

/**
 * Gemeinsamer Test für die PDF-Models aller Dokumenttypen (`GET /{dokumente}/{id}/pdf`).
 */
#[CoversClass(ConfirmationPdf::class)]
#[CoversClass(CreditNotePdf::class)]
#[CoversClass(DeliveryNotePdf::class)]
#[CoversClass(InvoicePdf::class)]
#[CoversClass(LetterPdf::class)]
#[CoversClass(OfferPdf::class)]
#[CoversClass(ReminderPdf::class)]
#[UsesClass(ScalarCaster::class)]
final class DocumentPdfTest extends TestCase
{
    /**
     * @param class-string<InvoicePdf|LetterPdf> $class
     */
    #[Test]
    #[DataProvider('models')]
    public function hydratesAllFields(string $class, string $parentProperty, string $parentKey): void
    {
        $pdf = $class::fromArray([
            'id' => 4882,
            $parentKey => 240,
            'created' => '2026-09-02T12:04:15+02:00',
            'filename' => 'dokument_123.pdf',
            'mimetype' => 'application/x-pdf',
            'filesize' => 70137,
            'base64file' => base64_encode('%PDF-1.4'),
        ]);

        self::assertSame(4882, $pdf->id);
        self::assertSame(240, new ReflectionProperty($pdf, $parentProperty)->getValue($pdf));
        self::assertSame('2026-09-02T12:04:15+02:00', $pdf->created?->format(DATE_ATOM));
        self::assertSame('dokument_123.pdf', $pdf->filename);
        self::assertSame('application/x-pdf', $pdf->mimeType);
        self::assertSame(70137, $pdf->fileSize);
        self::assertSame('%PDF-1.4', $pdf->getBinary());
    }

    /**
     * @param class-string<InvoicePdf|LetterPdf> $class
     */
    #[Test]
    #[DataProvider('models')]
    public function missingFieldsFallBackToDefaults(string $class, string $parentProperty, string $parentKey): void
    {
        $pdf = $class::fromArray([]);

        self::assertSame(0, $pdf->id);
        self::assertSame(0, new ReflectionProperty($pdf, $parentProperty)->getValue($pdf));
        self::assertNull($pdf->created);
        self::assertSame('', $pdf->filename);
        self::assertSame('application/pdf', $pdf->mimeType);
        self::assertSame(0, $pdf->fileSize);
        self::assertSame('', $pdf->base64file);
        self::assertSame('', $pdf->getBinary());
    }

    /**
     * @param class-string<InvoicePdf|LetterPdf> $class
     */
    #[Test]
    #[DataProvider('models')]
    public function invalidBase64YieldsEmptyBinary(string $class, string $parentProperty, string $parentKey): void
    {
        self::assertSame('', $class::fromArray(['base64file' => '%%%kein base64%%%'])->getBinary());
    }

    /**
     * @return iterable<string, array{class-string, string, string}>
     */
    public static function models(): iterable
    {
        yield 'Confirmation' => [ConfirmationPdf::class, 'confirmationId', 'confirmation_id'];
        yield 'CreditNote' => [CreditNotePdf::class, 'creditNoteId', 'credit_note_id'];
        yield 'DeliveryNote' => [DeliveryNotePdf::class, 'deliveryNoteId', 'delivery_note_id'];
        yield 'Invoice' => [InvoicePdf::class, 'invoiceId', 'invoice_id'];
        yield 'Letter' => [LetterPdf::class, 'letterId', 'letter_id'];
        yield 'Offer' => [OfferPdf::class, 'offerId', 'offer_id'];
        yield 'Reminder' => [ReminderPdf::class, 'reminderId', 'reminder_id'];
    }
}
