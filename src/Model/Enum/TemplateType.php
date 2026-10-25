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
    use FromApi;

    case DEFINED = 'DEFINED';
    case UPLOADED = 'UPLOADED';
}
