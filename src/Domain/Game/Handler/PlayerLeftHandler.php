<?php

namespace App\Domain\Game\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Handler\EventHandlerInterface;
use App\Domain\Game\Event\PlayerLeft;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class PlayerLeftHandler implements EventHandlerInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private WebSocketDispatcherInterface $dispatcher
    ) {
    }

    public function getType(): string
    {
        return 'player_left';
    }

    public function handle(PlayerLeft $payload, ?ConnectionInterface $conn = null): void
    {
        $sessionId = $payload['sessionId'];
        $playerId = $payload['departedPlayer'];
        $session = $this->sessionRepository->find($sessionId);

        if ($session === null) {
            throw new NotFoundException('Session is not found');
        }

        if (null !== $session['players'][$playerId]) {
            unset($session['players'][$playerId]);
            $this->sessionRepository->save($session);
        }

        if (count($session['players']) <= 1) {
            $this->dispatcher->dispatchFromArray([
                'type' => 'finish_session',
                'payload' => [
                    'sessionId' => $sessionId
                ]
            ]);
        }
    }
}
