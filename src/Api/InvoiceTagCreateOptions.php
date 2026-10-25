<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Typisierter Payload für POST /invoice-tags gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/rechnungen/schlagworte/
 *
 * Generiert aus tools/codegen/definitions/invoice-tags.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final class InvoiceTagCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * ID des Datensatzes, an dem das Schlagwort hängt.
         *
         * Billomat-Feld: invoice_id
         * Typ: INT
         */
        public int $invoiceId,
        /**
         * Name des Schlagworts.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
    ) {
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoiceId,
            'name' => $this->name,
        ];
    }
}
