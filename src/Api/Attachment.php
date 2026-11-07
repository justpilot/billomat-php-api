<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use InvalidArgumentException;

/**
 * Zusätzlicher Anhang für {@see EmailOptions} und {@see MailOptions}.
 *
 * Billomat erwartet den Inhalt base64-kodiert; {@see fromBinary()} und {@see fromFile()} übernehmen das.
 *
 * ```php
 * new EmailOptions(attachments: [Attachment::fromFile('/tmp/agb.pdf')]);
 * ```
 */
final readonly class Attachment
{
    /**
     * @param string $filename   Dateiname, wie ihn der Empfänger sieht
     * @param string $mimeType   MIME-Typ (Billomat-Feld `mimetype`)
     * @param string $base64File base64-kodierter Inhalt (Billomat-Feld `base64file`)
     */
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $base64File,
    ) {
    }

    /**
     * Anhang aus rohen Bytes.
     */
    public static function fromBinary(string $filename, string $content, string $mimeType = 'application/pdf'): self
    {
        return new self($filename, $mimeType, base64_encode($content));
    }

    /**
     * Anhang aus einer Datei; ohne `$filename` gilt der Name der Datei.
     *
     * @throws InvalidArgumentException wenn die Datei nicht lesbar ist
     */
    public static function fromFile(string $path, ?string $filename = null, string $mimeType = 'application/pdf'): self
    {
        $content = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
        if (false === $content) {
            throw new InvalidArgumentException(\sprintf('Attachment file "%s" is not readable.', $path));
        }

        return self::fromBinary($filename ?? basename($path), $content, $mimeType);
    }

    /**
     * @return array{filename: string, mimetype: string, base64file: string}
     */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'mimetype' => $this->mimeType,
            'base64file' => $this->base64File,
        ];
    }
}
