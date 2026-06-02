<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Typ einer Custom-Property-Definition.
 *
 * Doku: https://www.billomat.com/en/api/settings/client-properties/
 */
enum PropertyType: string
{
    case TEXTFIELD = 'TEXTFIELD';
    case TEXTAREA = 'TEXTAREA';
    case CHECKBOX = 'CHECKBOX';

    public static function fromApi(?string $value): ?self
    {
        return null === $value || '' === $value ? null : self::tryFrom($value);
    }
}
