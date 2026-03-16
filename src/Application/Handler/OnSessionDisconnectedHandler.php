<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\PlayerLeft;
use App\Domain\Session\Event\SessionDisconnected;

class OnSessionDisconnectedHandler extends AbstractEventHandler
{
    public function __construct(
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return SessionDisconnected::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var SessionDisconnected $event */
        $this->dispatcher->dispatch(
            new PlayerLeft(
                $event->getSessionId(),
                $event->getGameId(),
                $event->getPlayerToken()
            )
        );
    }
}
