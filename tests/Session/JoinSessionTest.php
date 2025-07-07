<?php

namespace Tests\Session;

use App\Infrastructure\Repository\Session\SessionRepositoryInterface;
use App\Infrastructure\Repository\Session\RedisGameSessionRepository;
use Tests\BaseWebSocketTestCase;

class JoinSessionTest extends BaseWebSocketTestCase
{
    private SessionRepositoryInterface $sessionRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->sessionRepository = $this->getFromContainer(RedisGameSessionRepository::class);
    }

    public function testCreateSessionAndJoin()
    {
        // 1. Первый игрок создает сессию
        $creatorClient = $this->getClient();
        $createResponse = $creatorClient->sendWebSocketMessage([
            'type' => 'create_session',
            'payload' => [
                'summary_type' => 'unique_words_by_length',
                'player' => 'Danil'
            ]
        ]);

        // Проверяем успешное создание
        $this->assertArrayStructure($createResponse[0], [
            'type' => 'session_created',
            'payload' => [
                'message' => 'Game session successfully created',
                'sessionId'
            ]
        ]);

        $sessionId = $createResponse[0]['payload']['sessionId'];

        // 2. Второй игрок присоединяется
        $joinerClient = $this->getClient();
        $joinResponse = $joinerClient->sendWebSocketMessage([
            'type' => 'join_session',
            'payload' => [
                'player' => 'Sinem',
                'sessionId' => $sessionId
            ]
        ]);

        // Проверяем ответ на присоединение
        $session = $this->sessionRepository->find($sessionId);
        $expectedJoinResponse = [
            'type' => 'session_joined',
            'payload' => [
                'message' => 'You joined the game session!',
                'sessionId' => $sessionId,
                'sessionWord' => $session['sessionWord'],
            ]
        ];

        // $expectedJoinResponse = [
        //     'type' => 'countdown',
        //     'payload' => [
        //         'startAt',
        //         'remainingSeconds',
        //     ]
        // ];

        $this->assertArrayStructure($joinResponse[0], $expectedJoinResponse);

        // 3. Проверяем, что сессия обновилась в репозитории
        $updatedSession = $this->sessionRepository->find($sessionId);
        $this->assertCount(2, $updatedSession['players']);
    }

    public function testJoinNonExistingSession()
    {
        $client = $this->getClient();
        $client->sendWebSocketMessage([
            'type' => 'join_session',
            'payload' => [
                'player' => 'Sinem',
                'sessionId' => 'non_existing_id'
            ]
        ]);

        $this->assertEquals([
            'type' => 'error',
            'payload' => [
                'message' => 'Session not found.',
                'code' => 404
            ]
        ], $client->getResponseMessages()[0]);
    }
}
