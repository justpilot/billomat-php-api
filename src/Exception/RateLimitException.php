<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Exception;

/**
 * Billomat hat mit 429 geantwortet: das Kontingent des aktuellen 15-Minuten-Fensters ist verbraucht.
 *
 * Das SDK wartet selbst nur, wenn die Wartezeit innerhalb von
 * {@see \Justpilot\Billomat\Config\RetryPolicy::$maxDelayMs} liegt.
 * Sonst landet diese Exception beim Aufrufer, der mit {@see getRetryAfter()} planen kann.
 * Kommt sie beim Aufrufer an, sind die Retries ausgeschöpft.
 */
class RateLimitException extends HttpException
{
}
