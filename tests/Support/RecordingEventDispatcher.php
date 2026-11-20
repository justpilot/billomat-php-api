<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Support;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * PSR-14-Dispatcher für Tests: merkt sich jedes Event.
 */
final class RecordingEventDispatcher implements EventDispatcherInterface
{
    /** @var list<object> */
    public array $events = [];

    public function dispatch(object $event): object
    {
        $this->events[] = $event;

        return $event;
    }

    /**
     * @return list<class-string>
     */
    public function classes(): array
    {
        return array_map(static fn (object $event): string => $event::class, $this->events);
    }
}
