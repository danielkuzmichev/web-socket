<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\PlayerJoined;
use App\Domain\Session\Event\SessionJoined;

class OnSessionJoinedHandler extends AbstractEventHandler
{
    public function __construct(
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return SessionJoined::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var SessionJoined $event */
        $sessionId = $event->getSessionId();
        $playerToken = $event->getPlayerToken();

        $this->dispatcher->dispatch(
            new PlayerJoined(
                $sessionId,
                $event->getProcessId(),
                $playerToken
            )
        );
    }
}
