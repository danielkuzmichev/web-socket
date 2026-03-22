<?php

namespace App\Application\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\WordRejected;
use App\Connection\ConnectionStorageInterface;

class NotifyPlayerOnWordRejectedHandler extends AbstractEventHandler
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage
    ) {
    }

    public function getEventClass(): string
    {
        return WordRejected::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var WordRejected $event */
        $this->connectionStorage->sendToToken($event->getPlayerToken(), [
            'type' => 'word_result',
            'payload' => [
                'message' => $event->getMessage(),
            ],
        ]);
    }
}
