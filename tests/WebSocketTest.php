<?php

use PHPUnit\Framework\TestCase;
use Wrench\Client;

class WebSocketTest extends TestCase
{
    public function testWebSocketResponds()
    {
        $client = new Client('ws://127.0.0.1:8080/', 'http://localhost', [
            'timeout' => 2,
        ]);

        $client->connect();

        $message = [
            'type' => 'create_session',
            'payload' => [
                'summary_type' => 'unique_words_by_length',
                'player' => 'Danil'
            ]
        ];


        $client->sendData(json_encode($message));

        $response = $client->receive();
        $responseString = $response[0]->getPayload();

        $this->assertJson($responseString, 'Response is not a valid JSON');

        $responseData = json_decode($responseString, true);

        $this->assertIsArray($responseData);
        $this->assertEquals('session_created', $responseData['type'] ?? null);
        $this->assertArrayHasKey('payload', $responseData);
        $this->assertArrayHasKey('message', $responseData['payload']);
        $this->assertEquals('Game session successfully created', $responseData['payload']['message'] ?? null);
        $this->assertArrayHasKey('sessionId', $responseData['payload']);
        $this->assertArrayHasKey('sessionWord', $responseData['payload']);
        var_dump($responseData['payload']['sessionId']);
    }
}
