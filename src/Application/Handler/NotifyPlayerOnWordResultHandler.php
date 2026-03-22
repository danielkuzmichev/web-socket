<?php

namespace App\Application\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\WordResult;
use App\Infrastructure\Connection\ConnectionStorageInterface;

class NotifyPlayerOnWordResultHandler extends AbstractEventHandler
{
    public function __construct(
        private ConnectionStorageInterface $connectionStorage
    ) {
    }

    public function getEventClass(): string
    {
        return WordResult::class;
    }

    protected function process(EventInterface $event): void
    {
        /** @var WordResult $event */
        $payload = [
            'message' => $event->getMessage(),
        ];

        if ($event->getScore() !== null) {
            $payload['score'] = $event->getScore();
        }

        if ($event->getTotalScore() !== null) {
            $payload['total'] = $event->getTotalScore();
        }

        $this->connectionStorage->sendToToken($event->getPlayerToken(), [
            'type' => 'word_result',
            'payload' => $payload,
        ]);
    }
}
