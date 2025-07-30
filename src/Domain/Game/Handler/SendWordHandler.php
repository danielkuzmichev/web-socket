<?php

namespace App\Domain\Game\Handler;

use App\Domain\Game\Service\WordServiceInterface;
use App\Core\Handler\EventHandlerInterface;
use App\Domain\Session\Repository\SessionRepositoryInterface;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class SendWordHandler implements EventHandlerInterface
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private WordServiceInterface $wordService
    ) {
    }

    public function getType(): string
    {
        return 'send_word';
    }

    public function handle(array $payload, ?ConnectionInterface $conn = null): void
    {
        $word = $payload['word'];

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
