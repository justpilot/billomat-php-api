<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum TemplateEngine: string
{
    use FromApi;

    case DEFAULT = 'DEFAULT';
}
