<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use DateTimeImmutable;

final class InvoicePdf
{
    /**
     * @param int $id Interne Billomat-ID des PDF-Eintrags
     * @param int $invoiceId ID der zugehörigen Rechnung
     * @param DateTimeImmutable|null $created Erstellungszeitpunkt des PDFs (kann zur Sicherheit null sein)
     * @param string $filename Dateiname, den Billomat vergeben hat
     * @param string $mimeType Mimetype, z. B. "application/pdf"
     * @param int $fileSize Dateigröße in Bytes
     * @param string $base64file Base64-kodierte PDF-Datei
     */
    public function __construct(
        public int                $id,
        public int                $invoiceId,
        public ?DateTimeImmutable $created,
        public string             $filename,
        public string             $mimeType,
        public int                $fileSize,
        public string             $base64file,
    )
    {
    }

    /**
     * Erzeugt ein InvoicePdf aus dem von Billomat gelieferten Array.
     *
     * Erwartete Struktur (JSON-Variante, Wrapper "pdf" schon entfernt):
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
     *
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $created = null;
        $createdRaw = $data['created'] ?? null;

        if (is_string($createdRaw) && $createdRaw !== '') {
            try {
                $created = new DateTimeImmutable($createdRaw);
            } catch (\Throwable) {
                // falls das Datum unerwartet formatiert ist, bleiben wir defensiv bei null
                $created = null;
            }
        }

        return new self(
            id: (int)($data['id'] ?? 0),
            invoiceId: (int)($data['invoice_id'] ?? 0),
            created: $created,
            filename: (string)($data['filename'] ?? ''),
            mimeType: (string)($data['mimetype'] ?? 'application/pdf'),
            fileSize: (int)($data['filesize'] ?? 0),
            base64file: (string)($data['base64file'] ?? ''),
        );
    }

    /**
     * Gibt den dekodierten PDF-Inhalt als Binär-String zurück.
     *
     * @return string Binärdaten des PDFs (z. B. für file_put_contents)
     */
    public function getBinary(): string
    {
        $decoded = base64_decode($this->base64file, true);

        return $decoded === false ? '' : $decoded;
    }
}