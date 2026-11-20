<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\EmailTemplateType;

/**
 * Typisierter Payload für PUT /email-templates/{id} gemäß Billomat-API.
 *
 * Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/email-vorlagen/
 *
 * Generiert aus tools/codegen/definitions/email-templates.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class EmailTemplateUpdateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Name der Vorlage.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string|Clear|null $name = null,
        /**
         * Betreff.
         *
         * Billomat-Feld: subject
         * Typ: ALNUM
         */
        public string|Clear|null $subject = null,
        /**
         * Nachrichtentext.
         *
         * Billomat-Feld: text
         * Typ: ALNUM
         */
        public string|Clear|null $body = null,
        /**
         * Standard-Vorlage für den Dokumenttyp?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         */
        public bool|Clear|null $isDefault = null,
        /**
         * Dokumenttyp, für den die Vorlage gilt.
         *
         * Mögliche Werte: INVOICES, OFFERS, CONFIRMATIONS, CREDIT_NOTES, DELIVERY_NOTES, REMINDERS, PAYMENT_THANKS
         *
         * Billomat-Feld: type
         * Typ: ALNUM („INVOICES“, „OFFERS“, „CONFIRMATIONS“, „CREDIT_NOTES“, „DELIVERY_NOTES“, „REMINDERS“, „PAYMENT_THANKS“)
         */
        public EmailTemplateType|Clear|null $type = null,
        /**
         * Erhält der Absender eine BCC-Kopie?
         *
         * Billomat-Feld: bcc
         * Typ: BOOL
         */
        public bool|Clear|null $bcc = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string|Clear|null $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `subject`. */
    public function withSubject(string|Clear|null $subject): self
    {
        return clone($this, ['subject' => $subject]);
    }

    /** Kopie mit geändertem `body`. */
    public function withBody(string|Clear|null $body): self
    {
        return clone($this, ['body' => $body]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(bool|Clear|null $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(EmailTemplateType|Clear|null $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `bcc`. */
    public function withBcc(bool|Clear|null $bcc): self
    {
        return clone($this, ['bcc' => $bcc]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * Nicht gesetzte Felder (null) fehlen im Payload, {@see Clear::Field} wird als null gesendet und leert das Feld.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $v): mixed => $v instanceof Clear ? null : $v,
            array_filter([
                'name' => $this->name,
                'subject' => $this->subject,
                'text' => $this->body,
                'is_default' => \is_bool($this->isDefault) ? ($this->isDefault ? 1 : 0) : $this->isDefault,
                'type' => $this->type instanceof EmailTemplateType ? $this->type->value : $this->type,
                'bcc' => \is_bool($this->bcc) ? ($this->bcc ? 1 : 0) : $this->bcc,
            ], static fn (mixed $v): bool => null !== $v),
        );
    }
}
