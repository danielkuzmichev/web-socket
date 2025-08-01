<?php

namespace App\Domain\Game\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\PlayerLeft;
use App\Domain\Session\Event\FinishSession;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class PlayerLeftHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getEventClass(): string
    {
        return PlayerLeft::class;
    }

    protected function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var PlayerLeft $event */
        $sessionId = $event->getSessionId();
        $playerId = $event->getPlayerId();
        $session = $this->sessionRepository->find($sessionId);

        if ($session === null) {
            throw new NotFoundException('Session is not found');
        }

        if (null !== $session['players'][$playerId]) {
            unset($session['players'][$playerId]);
            $this->sessionRepository->save($session);
        }

        if (count($session['players']) <= 1) {
            $this->dispatcher->dispatch(new FinishSession($sessionId));
        }
    }
}
