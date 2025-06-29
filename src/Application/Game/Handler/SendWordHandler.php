<?php

namespace App\Application\Game\Handler;

use App\Application\Game\Service\WordService;
use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class SendWordHandler implements MessageHandlerInterface
{
    public function __construct(
        private GameSessionRepositoryInterface $sessionRepository,
        private WordService $wordService
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

        $result = $this->wordService->score($word, $conn->resourceId, $session);

        if (!empty($result['score'])) {
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => 'found_word',
                    'score' => $result['score'],
                    'total' => $session['players'][$conn->resourceId]['score'] + $result['score']
                ]
            ]));
        } else {
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => 'not_found_word',
                ]
            ]));
        }
    }
}
