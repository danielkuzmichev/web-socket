<?php

namespace App\Core\Handler;

use App\Core\Event\EventInterface;

abstract class AbstractEventHandler implements EventHandlerInterface
{
    final public function handle(EventInterface $event): void
    {
        if (!$event instanceof ($this->getEventClass())) {
            throw new \InvalidArgumentException('Invalid event type for handler');
        }

        /** @var EventInterface $event */
        $this->process($event);
    }

    abstract public function getEventClass(): string;

    abstract protected function process(EventInterface $event): void;
}
