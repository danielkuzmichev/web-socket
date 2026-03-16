<?php

namespace App\Application\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Event\MatchStarted;
use App\Infrastructure\Connection\ConnectionStorageInterface;

class NotifyPlayersOnMatchStartedHandler extends AbstractEventHandler
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage
    ) {
    }

    public function getEventClass(): string
    {
        return MatchStarted::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var MatchStarted $event */
        $this->connectionStorage->broadcastToSession($event->getSessionId(), [
            'type' => 'match_started',
            'payload' => [
                'target_word' => $event->getTargetWord(),
            ]
        ]);
    }
}
