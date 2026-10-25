<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Config;

use InvalidArgumentException;

/**
 * Regeln für automatische Wiederholungen fehlgeschlagener Requests.
 *
 * Wiederholt wird
 *  - jeder Request bei 429 (Billomat hat ihn dann nicht verarbeitet),
 *  - GET-Requests bei 502/503/504 und bei Netzwerkfehlern.
 *
 * POST/PUT/DELETE werden außer bei 429 nie wiederholt, weil sie bei Billomat
 * nicht idempotent sind (z. B. PUT /invoices/{id}/complete).
 *
 * Die Wartezeit wächst exponentiell (`$baseDelayMs * 2^Versuch`, mit Jitter) bis
 * `$maxDelayMs`. Bei 429 gilt die von Billomat genannte Wartezeit (`Retry-After`
 * bzw. `X-Rate-Limit-Reset`); ist sie länger als `$maxDelayMs`, wird nicht gewartet,
 * sondern sofort eine {@see \Justpilot\Billomat\Exception\RateLimitException} geworfen.
 */
final readonly class RetryPolicy
{
    /**
     * @throws InvalidArgumentException bei negativen Werten
     */
    public function __construct(
        public int $maxRetries = 2,
        public int $baseDelayMs = 500,
        public int $maxDelayMs = 10_000,
    ) {
        if ($maxRetries < 0 || $baseDelayMs < 0 || $maxDelayMs < 0) {
            throw new InvalidArgumentException('Retry settings must not be negative.');
        }
    }

    /**
     * Keine automatischen Wiederholungen.
     */
    public static function none(): self
    {
        return new self(maxRetries: 0);
    }
}
