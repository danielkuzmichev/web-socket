<?php

namespace Tests;

use App\Infrastructure\Repository\GameSession\GameSessionRepositoryInterface;
use App\Infrastructure\Repository\GameSession\RedisGameSessionRepository;
use App\Util\Redis\RedisClient;
use PHPUnit\Framework\TestCase;
use Wrench\Client;

abstract class BaseWebSocketTestCase extends TestCase
{
    protected GameSessionRepositoryInterface $sessionRepository;
    protected Client $client;
    protected RedisClient $redis;

    public function setUp(): void
    {
        parent::setUp(); // Важно вызывать родительский setUp()

        // Инициализация Redis
        $this->redis = new RedisClient();
        $this->redis->connect('redis', 6379);

        // Инициализация WebSocket-клиента
        $this->client = new Client('ws://127.0.0.1:8080/', 'http://localhost', [
            'timeout' => 2,
        ]);
        $this->client->connect();
    }

    public function tearDown(): void
    {
        // Закрываем соединение (если нужно)
        if (isset($this->client)) {
            $this->client->disconnect();
        }

        parent::tearDown(); // Важно вызывать родительский tearDown()
    }

    /**
     * Отправляет сообщение в WebSocket и возвращает ответ.
     */
    protected function sendWebSocketMessage(array $message): array
    {
        $this->client->sendData(json_encode($message));
        $response = $this->client->receive();
        return $this->getArrayFromJson($response[0]->getPayload());
    }
}