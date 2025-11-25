<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

/**
 * Ressource wurde nicht gefunden (z. B. Client-ID existiert nicht).
 *
 * Typischer Statuscode:
 *  - 404 Not Found
 */
class NotFoundException extends HttpException
{
}