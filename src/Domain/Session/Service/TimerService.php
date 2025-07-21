<?php

namespace App\Domain\Session\Service;

use React\EventLoop\Loop;

class TimerService
{
    private array $timers = [];

    public function add(
        string $sessionId,
        float $delay,
        callable $callback,
        bool $periodic = false
    ): void {
        $timer = $periodic
            ? Loop::get()->addPeriodicTimer($delay, $callback)
            : Loop::get()->addTimer($delay, $callback);

        $this->timers[$sessionId][] = $timer;
    }

    public function cancelAll(string $sessionId): void
    {
        foreach ($this->timers[$sessionId] ?? [] as $timer) {
            Loop::get()->cancelTimer($timer);
        }
        unset($this->timers[$sessionId]);
    }

    public function cancelCurrent(string $sessionId): bool
    {
        if (empty($this->timers[$sessionId])) {
            return false;
        }

        $timer = array_pop($this->timers[$sessionId]);
        Loop::get()->cancelTimer($timer);
        return true;
    }
}
