<?php

namespace App\Domain\Game\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Service\WordServiceInterface;
use App\Core\Handler\EventHandlerInterface;
use App\Domain\Game\Event\SendWord;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class SendWordHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private WordServiceInterface $wordService
    ) {
    }

    public function getEventClass(): string
    {
        return SendWord::class;
    }

    public function process(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        /** @var SendWord $event*/
        $word = $event->getWord();

        $session = $this->sessionRepository->findByConnection($conn);
        if ($session === null) {
            throw new NotFoundException('No session for this connection');
        }

        if ($session['startAt'] >= microtime(true)) {
            throw new DomainLogicalException('You cannot send word early');
        }

        if (!$this->wordService->checkLetters($word, $session['sessionWord'])) {
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => 'The letter is missing from the target word',
                ]
            ]));

            return;
        }

        $result = $this->wordService->score($word, $conn->resourceId, $session);

        if (!empty($result['score'])) {
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => $result['message'],
                    'score' => $result['score'],
                    'total' => $session['players'][$conn->resourceId]['score'] + $result['score']
                ]
            ]));
        } else {
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => $result['message'],
                ]
            ]));
        }
    }
}
