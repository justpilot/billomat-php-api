<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Event;

use Justpilot\Billomat\Exception\BillomatException;
use Psr\Http\Message\RequestInterface;

/**
 * Der Request ist endgültig fehlgeschlagen; gleich danach wirft das SDK `$exception`.
 *
 * Kommt auch bei erwarteten Fehlern wie 404 aus `get()`, das daraus null macht.
 */
final readonly class RequestFailed
{
    public function __construct(
        public RequestInterface $request,
        public BillomatException $exception,
        /** Nummer des letzten Versuchs, ab 1. */
        public int $attempt,
    ) {
    }
}
