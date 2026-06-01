<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Dateiformat einer hochgeladenen Vorlage.
 *
 * Billomat: format (doc, docx, rtf)
 */
enum TemplateFormat: string
{
    case DOC = 'doc';
    case DOCX = 'docx';
    case RTF = 'rtf';

    public static function fromApi(?string $value): ?self
    {
        return null === $value ? null : (self::tryFrom($value) ?? null);
    }
}
