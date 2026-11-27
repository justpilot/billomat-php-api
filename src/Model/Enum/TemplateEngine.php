<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

enum TemplateEngine: string
{
    use FromApi;

    case DEFAULT = 'DEFAULT';

    /** Eigene Vorlage; von Billomat geliefert, aber nicht dokumentiert. */
    case OWN = 'OWN';
}
