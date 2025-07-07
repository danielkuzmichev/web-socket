<?php

namespace Tests\Session;

use App\Infrastructure\Repository\Session\SessionRepositoryInterface;
use App\Infrastructure\Repository\Session\RedisGameSessionRepository;
use Tests\BaseWebSocketTestCase;
use Tests\WebSocketClientDecorator;

class CreateSessionTest extends BaseWebSocketTestCase
{
    private SessionRepositoryInterface $sessionRepository;
    private WebSocketClientDecorator $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->sessionRepository = $this->getFromContainer(RedisGameSessionRepository::class);
        $this->client = $this->getClient();
    }

    public function testCreateSessionAndThenRejectDuplicate()
    {
        // 1. Первое создание сессии - должно быть успешно
        $firstResponse = $this->sendCreateSessionRequest()[0];

        $this->assertSessionCreatedSuccessfully($firstResponse);
        $sessionId = $firstResponse['payload']['sessionId'];
        $this->assertSessionExistsInRepository($sessionId);

        // 2. Повторная попытка создания - должна вернуть ошибку
        $secondResponse = $this->sendCreateSessionRequest()[1];

        $this->assertDuplicateSessionError($secondResponse);
    }

    private function sendCreateSessionRequest(): array
    {
        $message = [
            'type' => 'create_session',
            'payload' => [
                'summary_type' => 'unique_words_by_length',
                'player' => 'Danil'
            ]
        ];

        return $this->client->sendWebSocketMessage($message);
    }

    private function assertSessionCreatedSuccessfully(array $response): void
    {
        $expectedStructure = [
            'type' => 'session_created',
            'payload' => [
                'message' => 'Game session successfully created',
                'sessionId',
            ]
        ];

        $this->assertArrayStructure($response, $expectedStructure);
    }

    private function assertSessionExistsInRepository(string $sessionId): void
    {
        $session = $this->sessionRepository->find($sessionId);
        $this->assertNotEmpty($session, "Session {$sessionId} should exist in repository");
    }

    private function assertDuplicateSessionError(array $response): void
    {
        $expectedError = [
            'type' => 'error',
            'payload' => [
                'message' => 'You already created or joined a session.',
                'code' => 409,
            ]
        ];

        $this->assertEquals(
            $expectedError,
            $response,
            'Second attempt should return duplicate session error'
        );
    }
}
