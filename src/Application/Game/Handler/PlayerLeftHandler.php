<?php

namespace App\Application\Game\Handler;

use App\Core\Dispatcher\MessageDispatcherInterface;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\Session\SessionRepositoryInterface;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class PlayerLeftHandler implements MessageHandlerInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private MessageDispatcherInterface $dispatcher
    ) {
    }

    public function getType(): string
    {
        return 'player_left';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
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
