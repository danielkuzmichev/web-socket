<?php

namespace App\Application\Game\Handler;

use App\Core\Handler\MessageHandlerInterface;
use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface as GameSessionGameSessionRepositoryInterface;
use App\Infrastructure\Repository\Word\WordRepositoryInterface;
use App\Util\Exception\DomainLogicalException;
use App\Util\Exception\NotFoundException;
use Ratchet\ConnectionInterface;

class SendWordHandler implements MessageHandlerInterface
{
    public function __construct(
        private GameSessionGameSessionRepositoryInterface $sessionRepository,
        private WordRepositoryInterface $wordRepository
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

        $wordExists = $this->wordRepository->exists($word);

        if ($wordExists) {
            $score = mb_strlen($word);
            $connectionId = $conn->resourceId;

            $player = &$session['players'][$connectionId];

            if (!in_array($word, $player['words'], true)) {
                $player['words'][] = $word;
                if (isset($player['score'])) {
                    $player['score'] = $player['score'] + $score;
                } else {
                    $player['score'] = $score;
                }
            }

            $session['players'][$connectionId] = $player;

            $this->sessionRepository->save($session);
            $conn->send(json_encode([
                'type' => 'word_result',
                'payload' => [
                    'message' => 'found_word',
                    'score' => $score,
                    'total' => $player['score']
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
