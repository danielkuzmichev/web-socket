<?php

namespace App\Handler;

use Ratchet\ConnectionInterface;

interface MessageHandlerInterface {

    public function getType(): string;

    public function handle(array $payload, ConnectionInterface $conn): void;

}
