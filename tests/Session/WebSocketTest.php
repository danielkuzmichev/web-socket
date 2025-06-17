<?php

namespace Tests\Session;

use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Infrastructure\Repository\GameSession\RedisGameSessionRepository;
use Tests\BaseWebSocketTestCase;

class WebSocketTest extends BaseWebSocketTestCase
{
    private GameSessionRepositoryInterface $sessionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->sessionRepository = $this->getFromContainer(RedisGameSessionRepository::class);
    }

    public function testWebSocketResponds()
    {

        $message = [
            'type' => 'create_session',
            'payload' => [
                'summary_type' => 'unique_words_by_length',
                'player' => 'Danil'
            ]
        ];

        $responseData = $this->sendWebSocketMessage($message);

        $expectedStructure = [
            'type' => 'session_created',
            'payload' => [
                'message' => 'Game session successfully created',
                'sessionId',
            ]
        ];

        $this->assertArrayStructure($responseData, $expectedStructure);

        $session = $this->sessionRepository->find($responseData['payload']['sessionId']);
        $this->assertNotEmpty($session);
    }
}
