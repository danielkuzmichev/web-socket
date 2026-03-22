<?php

namespace Tests\Session;

use Tests\BaseWebSocketTestCase;
use WebSocketClientDecorator;
use ReflectionClass;

class FullGameProcessTest extends BaseWebSocketTestCase
{
    public function testFullGameProcess()
    {
        // 1. Первый игрок создает игру
        $player1Token = 'player1';
        $player2Token = 'player2';
        $summaryType = 'total_score';

        $client1 = $this->getClient();
        $client2 = $this->getClient();

        $createGameMsg = [
            'type' => 'create_game',
            'payload' => [
                'summaryType' => $summaryType,
                'playerToken' => $player1Token,
            ]
        ];
        $response1 = $client1->sendWebSocketMessage($createGameMsg);

        $sessionCreated = $response1[0];
        $this->assertEquals('session_created', $sessionCreated['type']);
        $sessionId = $sessionCreated['payload']['sessionId'];

        // 2. Второй игрок присоединяется
        $joinMsg = [
            'type' => 'join_session',
            'payload' => [
                'sessionId' => $sessionId,
                'playerToken' => $player2Token,
            ]
        ];
        $response2 = $client2->sendWebSocketMessage($joinMsg);
        $this->assertEquals('session_joined', $response2[0]['type']);

        // 3. Ждем 6 секунд (имитация ожидания начала игры)
        sleep(6);
        $messages = $client2->receive();
        $this->assertGreaterThanOrEqual(2, count($messages));
        $gameWordMsg = null;
        foreach ($messages as $message) {
            if (
                ($message['type'] ?? null) === 'match_started'
                && isset($message['payload']['target_word'])
            ) {
                $gameWordMsg = $message;
                break;
            }
        }

        $this->assertNotNull($gameWordMsg, 'Message match_started must be present');
        $this->assertArrayHasKey('payload', $gameWordMsg);
        $this->assertArrayHasKey('target_word', $gameWordMsg['payload']);

        $originalWord = $gameWordMsg['payload']['target_word'];

        // 4. Подменяем слово в объекте игры через сеттер

        $sessionRepository = $this->getFromContainer(\App\Session\Repository\SessionRepositoryInterface::class);

        $session = $sessionRepository->find($sessionId);

        $gameId = $session->getProcessId();

        $gameRepository = $this->getFromContainer(\App\Game\Repository\GameRepositoryInterface::class);

        $game = $gameRepository->find($gameId);
        $reflection = new \ReflectionClass($game);
        $wordProp = $reflection->getProperty('word');
        $wordProp->setAccessible(true);
        $wordProp->setValue($game, 'пароход');

        //$game->setWord('пароход');
        $gameRepository->save($game);

        // 5. Второй игрок отправляет слово
        $sendWordMsg = [
            'type' => 'send_word',
            'payload' => [
                'word' => 'ход',
                'playerToken' => $player2Token,
                'sessionId' => $sessionId,
            ]
        ];
        $wordResponses = $client2->sendWebSocketMessage($sendWordMsg);

        // 6. Ждем завершения матча (15 секунд)
        sleep(15);
        $finalMessages = $client2->receive();

        $found = false;
        foreach ($finalMessages as $msg) {
            if (
                isset($msg['type']) && $msg['type'] === 'summarize_results' &&
                isset($msg['payload']['results']) && $msg['payload']['results'] === true
            ) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Second player must win');
    }
}
