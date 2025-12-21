<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ der Vorlage.
 *
 * Billomat: template_type (DEFINED = editor, UPLOADED = hochgeladen)
 */
enum TemplateType: string
{
    case DEFINED = 'DEFINED';
    case UPLOADED = 'UPLOADED';

    public static function fromApi(?string $value): ?self
    {
        return $value === null ? null : (self::tryFrom($value) ?? null);
    }
}