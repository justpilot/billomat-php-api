<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Internal\ScalarCaster;

use const DATE_ATOM;

/**
 * PDF eines Billomat-Dokuments als JSON: GET /invoices/{id}/pdf, /offers/{id}/pdf, /confirmations/{id}/pdf,
 * /credit-notes/{id}/pdf, /delivery-notes/{id}/pdf, /letters/{id}/pdf und /reminders/{id}/pdf.
 *
 * Alle Dokumenttypen liefern dieselbe Struktur; nur der Name des ID-Felds des Dokuments
 * (`invoice_id`, `offer_id`, …) unterscheidet sich und landet in `$documentId`.
 *
 * Erwartete Struktur (Wrapper "pdf" schon entfernt):
 *
 * [
 *   "id"         => 4882,
 *   "created"    => "2009-09-02T12:04:15+02:00",
 *   "invoice_id" => 240,
 *   "filename"   => "invoice_123.pdf",
 *   "mimetype"   => "application/pdf",
 *   "filesize"   => 70137,
 *   "base64file" => "{base64-kodiertes PDF}",
 * ]
 */
final readonly class DocumentPdf
{
    /**
     * Billomat-Felder mit der ID des Dokuments, je nach Endpunkt genau eines; `document_id` liest die
     * Ausgabe von {@see toArray()} zurück.
     */
    private const array DOCUMENT_ID_FIELDS = [
        'invoice_id', 'offer_id', 'confirmation_id', 'credit_note_id', 'delivery_note_id', 'letter_id', 'reminder_id', 'document_id',
    ];

    private const array FIELDS = ['id', 'created', 'filename', 'mimetype', 'filesize', 'base64file', ...self::DOCUMENT_ID_FIELDS];

    /**
     * @param int                    $id         interne Billomat-ID des PDF-Eintrags
     * @param int                    $documentId ID des Dokuments (Billomat-Feld `invoice_id`, `offer_id`, …)
     * @param DateTimeImmutable|null $created    Erstellungszeitpunkt des PDFs
     * @param string                 $filename   Dateiname, den Billomat vergeben hat
     * @param string                 $mimeType   MIME-Typ (Billomat-Feld `mimetype`), fehlt er, `application/pdf`
     * @param int|null               $fileSize   Dateigröße in Bytes (Billomat-Feld `filesize`)
     * @param string                 $base64File base64-kodierter Inhalt (Billomat-Feld `base64file`)
     * @param array<string, mixed>   $extra      Felder, die dieses Model nicht kennt
     */
    public function __construct(
        public int $id,
        public int $documentId,
        public ?DateTimeImmutable $created,
        public string $filename,
        public string $mimeType,
        public ?int $fileSize,
        public string $base64File,
        public array $extra = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws UnexpectedResponseException wenn `id`, die Dokument-ID, `filename` oder `base64file` fehlt
     */
    public static function fromArray(array $data): self
    {
        $documentId = null;
        foreach (self::DOCUMENT_ID_FIELDS as $field) {
            $documentId ??= ScalarCaster::toIntOrNull($data[$field] ?? null);
        }

        return new self(
            id: ScalarCaster::required(ScalarCaster::toIntOrNull($data['id'] ?? null), self::class, 'id'),
            documentId: ScalarCaster::required($documentId, self::class, implode('/', self::DOCUMENT_ID_FIELDS)),
            created: ScalarCaster::toDateTimeOrNull($data['created'] ?? null),
            filename: ScalarCaster::required(ScalarCaster::toStringOrNull($data['filename'] ?? null), self::class, 'filename'),
            mimeType: ScalarCaster::toStringOrNull($data['mimetype'] ?? null) ?? 'application/pdf',
            fileSize: ScalarCaster::toIntOrNull($data['filesize'] ?? null),
            base64File: ScalarCaster::required(ScalarCaster::toStringOrNull($data['base64file'] ?? null), self::class, 'base64file'),
            extra: array_diff_key($data, array_flip(self::FIELDS)),
        );
    }

    /**
     * Für Debug- und Log-Ausgaben; die Dokument-ID steht unter `document_id`, den Inhalt kürzt es nicht.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'id' => $this->id,
            'document_id' => $this->documentId,
            'created' => $this->created?->format(DATE_ATOM),
            'filename' => $this->filename,
            'mimetype' => $this->mimeType,
            'filesize' => $this->fileSize,
            'base64file' => $this->base64File,
        ];
    }

    /**
     * Dekodierter PDF-Inhalt als Binär-String (z. B. für file_put_contents).
     *
     * @throws UnexpectedResponseException wenn `base64file` kein gültiges Base64 ist
     */
    public function getBinary(): string
    {
        $binary = base64_decode($this->base64File, true);

        return false !== $binary
            ? $binary
            : throw new UnexpectedResponseException('Unexpected response from Billomat: DocumentPdf with an invalid "base64file" field.');
    }
}
