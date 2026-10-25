<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

/**
 * Options, die als Request-Body an Billomat gehen (Create/Update).
 */
interface WriteOptions
{
    /**
     * Serialisiert die Options in ein Billomat-Payload-Array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array;
}
