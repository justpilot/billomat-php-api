<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

/**
 * Die gesendeten Daten waren ungültig.
 *
 * Typische Statuscodes:
 *  - 400 Bad Request
 *  - 422 Unprocessable Entity
 */
class ValidationException extends HttpException
{
}