<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\DocumentPdf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use const DATE_ATOM;

/**
 * PDF-Model aller Dokumenttypen (`GET /{dokumente}/{id}/pdf`).
 */
#[CoversClass(DocumentPdf::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class DocumentPdfTest extends TestCase
{
    #[Test]
    #[DataProvider('documentIdFields')]
    public function hydratesAllFields(string $documentIdField): void
    {
        $pdf = DocumentPdf::fromArray([
            'id' => '4882',
            $documentIdField => '240',
            'created' => '2026-09-02T12:04:15+02:00',
            'filename' => 'dokument_123.pdf',
            'mimetype' => 'application/x-pdf',
            'filesize' => '70137',
            'base64file' => base64_encode('%PDF-1.4'),
        ]);

        self::assertSame(4882, $pdf->id);
        self::assertSame(240, $pdf->documentId);
        self::assertSame('2026-09-02T12:04:15+02:00', $pdf->created?->format(DATE_ATOM));
        self::assertSame('dokument_123.pdf', $pdf->filename);
        self::assertSame('application/x-pdf', $pdf->mimeType);
        self::assertSame(70137, $pdf->fileSize);
        self::assertSame(base64_encode('%PDF-1.4'), $pdf->base64File);
        self::assertSame('%PDF-1.4', $pdf->getBinary());
        self::assertSame([], $pdf->extra);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function documentIdFields(): iterable
    {
        foreach (['invoice_id', 'offer_id', 'confirmation_id', 'credit_note_id', 'delivery_note_id', 'letter_id', 'reminder_id'] as $field) {
            yield $field => [$field];
        }
    }

    #[Test]
    public function optionalFieldsHaveDefaults(): void
    {
        $pdf = DocumentPdf::fromArray(['id' => 1, 'invoice_id' => 2, 'filename' => 'x.pdf', 'base64file' => 'QUJD']);

        self::assertNull($pdf->created);
        self::assertSame('application/pdf', $pdf->mimeType);
        self::assertNull($pdf->fileSize);
    }

    #[Test]
    public function unknownFieldsLandInExtraAndToArrayKeepsThem(): void
    {
        $pdf = DocumentPdf::fromArray([
            'id' => 1,
            'invoice_id' => 2,
            'created' => '2026-09-02T12:04:15+02:00',
            'filename' => 'x.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => 3,
            'base64file' => 'QUJD',
            'neues_feld' => 'wert',
        ]);

        self::assertSame(['neues_feld' => 'wert'], $pdf->extra);
        self::assertSame([
            'neues_feld' => 'wert',
            'id' => 1,
            'document_id' => 2,
            'created' => '2026-09-02T12:04:15+02:00',
            'filename' => 'x.pdf',
            'mimetype' => 'application/pdf',
            'filesize' => 3,
            'base64file' => 'QUJD',
        ], $pdf->toArray());
    }

    /**
     * @param array<string, mixed> $data
     */
    #[Test]
    #[DataProvider('incompleteResponses')]
    public function missingRequiredFieldsThrow(array $data, string $field): void
    {
        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains($field);

        DocumentPdf::fromArray($data);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function incompleteResponses(): iterable
    {
        $complete = ['id' => 1, 'invoice_id' => 2, 'filename' => 'x.pdf', 'base64file' => 'QUJD'];

        yield 'ohne id' => [array_diff_key($complete, ['id' => true]), '"id"'];
        yield 'ohne Dokument-ID' => [array_diff_key($complete, ['invoice_id' => true]), 'invoice_id/offer_id'];
        yield 'ohne filename' => [array_diff_key($complete, ['filename' => true]), '"filename"'];
        yield 'ohne base64file' => [array_diff_key($complete, ['base64file' => true]), '"base64file"'];
        yield 'leeres base64file' => [['base64file' => ''] + $complete, '"base64file"'];
    }

    #[Test]
    public function invalidBase64Throws(): void
    {
        $pdf = DocumentPdf::fromArray(['id' => 1, 'invoice_id' => 2, 'filename' => 'x.pdf', 'base64file' => '%%%kein base64%%%']);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('invalid "base64file"');

        $pdf->getBinary();
    }

    #[Test]
    public function decodesBinaryContentExactly(): void
    {
        $original = "Binary\x00content\x01with\x02bytes";
        $pdf = DocumentPdf::fromArray(['id' => 1, 'invoice_id' => 2, 'filename' => 'x.pdf', 'base64file' => base64_encode($original)]);

        self::assertSame($original, $pdf->getBinary());
    }
}
