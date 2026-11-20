<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Support;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * PSR-3-Logger für Tests: merkt sich Level, Nachricht und Kontext.
 */
final class RecordingLogger extends AbstractLogger
{
    /** @var list<array{level: string, message: string, context: array<mixed>}> */
    public array $logs = [];

    /**
     * @param array<mixed> $context
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => \is_string($level) ? $level : '', 'message' => (string) $message, 'context' => $context];
    }

    /**
     * @return list<string>
     */
    public function levels(): array
    {
        return array_column($this->logs, 'level');
    }
}
