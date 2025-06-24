<?php

namespace Tests;

use Wrench\Client;

class WebSocketClientDecorator
{
    private Client $client;
    private array $responseStack = [];

    /** @todo добавить DI */
    public function __construct()
    {
        $this->client = new Client('ws://127.0.0.1:8080/', 'http://localhost', [
            'timeout' => 2,
        ]);
        $this->client->connect();
    }

    public function sendWebSocketMessage(array $message): array
    {
        $this->client->sendData(json_encode($message));
        $response = $this->client->receive();
        $this->updateResponses($response);

        return $this->getResponseMessages();
    }

    public function receive(): array
    {
        $response = $this->client->receive();
        $this->updateResponses($response);

        return $this->getResponseMessages();
    }

    private function updateResponses(array $data): void
    {
        array_push($this->responseStack, ...$data);
    }

    public function getResponseMessages(): array
    {
        $messages = [];
        foreach ($this->responseStack as $response) {
            $messages[] = json_decode($response->getPayload(), true);
        }

        return $messages;
    }

    public function getResponsePayloads(): array
    {
        return $this->responseStack;
    }

    public function __destruct()
    {
        $this->client->disconnect();
    }
}
