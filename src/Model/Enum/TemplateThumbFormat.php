<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model\Enum;

/**
 * Bildformat für /templates/{id}/thumb.
 *
 * Billomat: format (png, gif, jpg)
 */
enum TemplateThumbFormat: string
{
    use FromApi;

    case PNG = 'png';
    case GIF = 'gif';
    case JPG = 'jpg';
}
