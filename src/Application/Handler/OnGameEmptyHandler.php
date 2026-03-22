<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\GameEmpty;
use App\Domain\Session\Event\FinishSession;

class OnGameEmptyHandler extends AbstractEventHandler
{
    public function __construct(
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return GameEmpty::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var GameEmpty $event */
        $this->dispatcher->dispatch(new FinishSession($event->getSessionId()));
    }
}
