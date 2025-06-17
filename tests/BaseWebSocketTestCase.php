<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Trait\JsonAssertionsTrait;
use Wrench\Client;

abstract class BaseWebSocketTestCase extends TestCase
{
    use JsonAssertionsTrait;

    protected Client $client;
    private static $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->initWebSocketClient();
        self::$container = $GLOBALS['TEST_CONTAINER'];
    }

    protected function initWebSocketClient(): void
    {
        $this->client = new Client('ws://127.0.0.1:8080/', 'http://localhost', [
            'timeout' => 2,
        ]);
        $this->client->connect();
    }

    protected function sendWebSocketMessage(array $message): array
    {
        $this->client->sendData(json_encode($message));
        $response = $this->client->receive();
        return $this->getArrayFromJson($response[0]->getPayload());
    }

    public function tearDown(): void
    {
        if (isset($this->client)) {
            $this->client->disconnect();
        }
        parent::tearDown();
    }

    public function getContainer(): mixed 
    {
        return self::$container;
    }

    public function getFromContainer(string $class): mixed
    {
        return $this->getContainer()->get($class);
    }
}