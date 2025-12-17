<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum TemplateEngine: string
{
    case DEFAULT = 'DEFAULT';

    public static function fromApi(?string $value): ?self
    {
        return $value !== null ? self::tryFrom($value) : null;
    }
}