<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\CreatedGame;
use App\Domain\Session\Event\CreateSession;

class CreateSessionOnGameCreatedHandler extends AbstractEventHandler
{
    public function __construct(
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return CreatedGame::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var CreatedGame $event */
        $this->dispatcher->dispatch(
            new CreateSession(
                $event->getGameId(),
                $event->getCountOfConnections(),
                $event->getPlayerToken()
            )
        );
    }
}
