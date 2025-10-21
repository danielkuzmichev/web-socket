<?php

namespace App\Core\Dispatcher;

use App\Core\Event\EventInterface;
use App\Core\Handler\EventHandlerInterface;
use Ratchet\ConnectionInterface;

class EventDispatcher implements WebSocketDispatcherInterface
{
    /**
     * @var array<class-string<EventInterface>, EventHandlerInterface[]>
     */
    private array $handlers = [];

    /**
     * @var array<string, class-string<EventInterface>>
     */
    private array $eventClassMap = [];

    /**
     * @param iterable<EventHandlerInterface> $handlers
     * @param array<string, class-string<EventInterface>> $eventClassMap
     */
    public function __construct(iterable $handlers = [], array $eventClassMap = [])
    {
        $this->eventClassMap = $eventClassMap;
        $this->setHandlers($handlers);
    }

    public function setHandlers(iterable $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    public function registerHandler(EventHandlerInterface $handler): void
    {
        if (method_exists($handler, 'getEventClass')) {
            $eventClasses = $handler->getEventClass();

            if (!is_array($eventClasses)) {
                $eventClasses = [$eventClasses];
            }

            foreach ($eventClasses as $eventClass) {
                if (!is_subclass_of($eventClass, EventInterface::class)) {
                    throw new \InvalidArgumentException("Invalid event class: {$eventClass}");
                }

                $this->handlers[$eventClass][] = $handler;
            }
        } else {
            throw new \LogicException("Handler must implement getEventClass()");
        }
    }

    public function dispatchFromArray(array $message, ?ConnectionInterface $conn = null): void
    {
        if (!isset($message['type'])) {
            throw new \InvalidArgumentException("Missing 'type' in message");
        }

        $type = $message['type'];
        $payload = $message['payload'] ?? [];

        $event = $this->createEvent($type, $payload);
        $this->dispatch($event, $conn);
    }

    protected function createEvent(string $type, array $payload): EventInterface
    {
        if (!isset($this->eventClassMap[$type])) {
            throw new \InvalidArgumentException("Unknown event type '{$type}'");
        }

        $eventClass = $this->eventClassMap[$type];

        return new $eventClass(...$payload);
    }

    public function dispatch(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        $eventClass = get_class($event);

        if (empty($this->handlers[$eventClass])) {
            throw new \RuntimeException("No handlers registered for event: {$eventClass}");
        }

        foreach ($this->handlers[$eventClass] as $handler) {
            $handler->handle($event, $conn);
        }
    }
}
