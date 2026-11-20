<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\Enum\EmailTemplateType;

/**
 * Typisierter Payload für POST /email-templates gemäß Billomat-API.
 *
 * Doku:
 * https://www.billomat.com/api/einstellungen/email-vorlagen/
 *
 * Generiert aus tools/codegen/definitions/email-templates.php – nicht von Hand bearbeiten,
 * sondern die Definition anpassen und `composer generate` ausführen.
 */
final readonly class EmailTemplateCreateOptions implements WriteOptions
{
    public function __construct(
        /**
         * Name der Vorlage.
         *
         * Billomat-Feld: name
         * Typ: ALNUM
         */
        public string $name,
        /**
         * Dokumenttyp, für den die Vorlage gilt.
         *
         * Mögliche Werte: INVOICES, OFFERS, CONFIRMATIONS, CREDIT_NOTES, DELIVERY_NOTES, REMINDERS, PAYMENT_THANKS
         *
         * Billomat-Feld: type
         * Typ: ALNUM („INVOICES“, „OFFERS“, „CONFIRMATIONS“, „CREDIT_NOTES“, „DELIVERY_NOTES“, „REMINDERS“, „PAYMENT_THANKS“)
         */
        public EmailTemplateType $type,
        /**
         * Betreff.
         *
         * Billomat-Feld: subject
         * Typ: ALNUM
         */
        public ?string $subject = null,
        /**
         * Nachrichtentext.
         *
         * Billomat-Feld: text
         * Typ: ALNUM
         */
        public ?string $body = null,
        /**
         * Standard-Vorlage für den Dokumenttyp?
         *
         * Billomat-Feld: is_default
         * Typ: BOOL
         * Default: nein
         */
        public ?bool $isDefault = null,
        /**
         * Erhält der Absender eine BCC-Kopie?
         *
         * Billomat-Feld: bcc
         * Typ: BOOL
         * Default: nein
         */
        public ?bool $bcc = null,
    ) {
    }

    /** Kopie mit geändertem `name`. */
    public function withName(string $name): self
    {
        return clone($this, ['name' => $name]);
    }

    /** Kopie mit geändertem `type`. */
    public function withType(EmailTemplateType $type): self
    {
        return clone($this, ['type' => $type]);
    }

    /** Kopie mit geändertem `subject`. */
    public function withSubject(?string $subject): self
    {
        return clone($this, ['subject' => $subject]);
    }

    /** Kopie mit geändertem `body`. */
    public function withBody(?string $body): self
    {
        return clone($this, ['body' => $body]);
    }

    /** Kopie mit geändertem `isDefault`. */
    public function withIsDefault(?bool $isDefault): self
    {
        return clone($this, ['isDefault' => $isDefault]);
    }

    /** Kopie mit geändertem `bcc`. */
    public function withBcc(?bool $bcc): self
    {
        return clone($this, ['bcc' => $bcc]);
    }

    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'subject' => $this->subject,
            'text' => $this->body,
            'is_default' => null === $this->isDefault ? null : ($this->isDefault ? 1 : 0),
            'type' => $this->type->value,
            'bcc' => null === $this->bcc ? null : ($this->bcc ? 1 : 0),
        ], static fn (mixed $v): bool => null !== $v);
    }
}
