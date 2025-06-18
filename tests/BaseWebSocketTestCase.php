<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Trait\JsonAssertionsTrait;
use Wrench\Client;

abstract class BaseWebSocketTestCase extends TestCase
{
    use JsonAssertionsTrait;

    protected Client $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->initWebSocketClient();
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
        return $this->getArrayFromJson(end($response)->getPayload());
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
        return TestContainerLocator::get();
    }

    public function getFromContainer(string $class): mixed
    {
        return $this->getContainer()->get($class);
    }
}
