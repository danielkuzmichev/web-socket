<?php

namespace App\Application\Handler;

use App\Core\Dispatcher\WebSocketDispatcherInterface;
use App\Core\Event\EventInterface;
use App\Core\Handler\AbstractEventHandler;
use App\Application\Event\SendWord;
use App\Application\Event\WordRejected;
use App\Application\Event\WordResult;
use App\Domain\Game\Repository\GameRepositoryInterface;
use App\Domain\Game\Service\WordServiceInterface;
use App\Domain\Session\Entity\Session;
use App\Domain\Session\Service\SessionServiceInterface;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\NotFoundException;
use DateTime;

class SendWordHandler extends AbstractEventHandler
{
    public function __construct(
        private SessionServiceInterface $sessionService,
        private GameRepositoryInterface $gameRepository,
        private WordServiceInterface $wordService,
        private WebSocketDispatcherInterface $dispatcher,
    ) {
    }

    public function getEventClass(): string
    {
        return SendWord::class;
    }

    public function process(EventInterface $event): void
    {
        /** @var SendWord $event*/
        $word = $event->getWord();
        $playerToken = $event->getPlayerToken();
        /** @var Session $session */
        $sessionId = $this->sessionService->findByPlayerToken($playerToken);
        $session = $this->sessionService->find($sessionId);
        if ($session === null) {
            throw new NotFoundException('No session for this connection');
        }

        if ($session->getStartAt() >= new DateTime()) {
            throw new DomainLogicalException('You cannot send word early');
        }

        $game = $this->gameRepository->find($session->getProcessId());

        if (!$this->wordService->checkLetters($word, $game->getWord())) {
            $this->dispatcher->dispatch(
                new WordRejected(
                    $sessionId,
                    $playerToken,
                    'The letter is missing from the target word'
                )
            );

            return;
        }

        $result = $this->wordService->score($word, $playerToken, $game);

        $score = $result['score'] ?? null;
        $message = $result['message'] ?? 'Word processed';
        $totalScore = $score !== null
            ? $game->getPlayerByKey($playerToken)?->getScore()
            : null;

        $this->dispatcher->dispatch(
            new WordResult(
                $sessionId,
                $playerToken,
                $message,
                $score,
                $totalScore
            )
        );
    }
}
