<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

/**
 * Fehler bei Authentifizierung oder Berechtigungen.
 *
 * Typische Statuscodes:
 *  - 401 Unauthorized
 *  - 403 Forbidden
 */
class AuthenticationException extends HttpException
{
}