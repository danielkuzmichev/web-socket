<?php

namespace App\Domain\Game\Handler;

use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Domain\Game\Service\WordServiceInterface;
use App\Domain\Game\Event\SendWord;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Session\Service\SessionServiceInterface;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\NotFoundException;
use DateTime;
use Ratchet\ConnectionInterface;
use App\Domain\Session\Entity\Session;

class SendWordHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private GameRepositoryInterface $gameRepository,
        private WordServiceInterface $wordService,
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
        /** @var Session $session */
        $session = $this->sessionService->findByConnection($conn);
        if ($session === null) {
            throw new NotFoundException('No session for this connection');
        }

        if ($session->getStartAt() >= new DateTime()) {
            throw new DomainLogicalException('You cannot send word early');
        }

        $game = $this->gameRepository->find($session->getProcessId());

        if (!$this->wordService->checkLetters($word, $game->getWord())) {
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => 'The letter is missing from the target word',
                ]
            ]));

            return;
        }

        $result = $this->wordService->score($word, $conn->resourceId, $game);

        if (!empty($result['score'])) {
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => $result['message'],
                    'score' => $result['score'],
                    'total' => $game->getPlayerByKey($conn->resourceId)->getScore()
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
