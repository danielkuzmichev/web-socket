<?php

namespace App\Core\Handler;

use App\Core\Event\EventInterface;
use Ratchet\ConnectionInterface;

abstract class AbstractEventHandler implements EventHandlerInterface
{
    final public function handle(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        if (!$event instanceof ($this->getEventClass())) {
            throw new \InvalidArgumentException('Invalid event type for handler');
        }

        /** @var EventInterface $event */
        $this->process($event, $conn);
    }

    abstract public function getEventClass(): string;

    abstract protected function process(EventInterface $event, ?ConnectionInterface $conn = null): void;
}
