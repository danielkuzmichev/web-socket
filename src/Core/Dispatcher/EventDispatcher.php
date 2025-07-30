<?php

namespace App\Core\Dispatcher;

use App\Core\Event\EventInterface;
use Ratchet\ConnectionInterface;

class EventDispatcher
{
    /**
     * @var array<string, object[]>  // eventClass => [handlers]
     */
    private array $handlers = [];

    /**
     * @var array<string, class-string<EventInterface>>
     * Маппинг типа из массива в класс события
     */
    private array $eventClassMap = [];

    /**
     * @param iterable<object> $handlers
     * @param array<string, class-string<EventInterface>> $eventClassMap
     */
    public function __construct(iterable $handlers = [], array $eventClassMap = [])
    {
        $this->setHandlers($handlers);
        $this->eventClassMap = $eventClassMap;
    }

    public function setHandlers(iterable $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    public function registerHandler(object $handler): void
    {
        $refClass = new \ReflectionClass($handler);

        foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $params = $method->getParameters();

            if (count($params) === 0) {
                continue;
            }

            $paramType = $params[0]->getType();

            if (!$paramType || $paramType->isBuiltin()) {
                continue;
            }

            $eventClass = $paramType->getName();

            if (!is_subclass_of($eventClass, EventInterface::class)) {
                continue;
            }

            $this->handlers[$eventClass][] = [$handler, $method->getName()];
        }
    }

    /**
     * Преобразует массив с type/payload в объект-событие и диспатчит его
     *
     * @param array $message ['type' => string, 'payload' => array]
     * @param ConnectionInterface|null $conn
     */
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

    /**
     * Создаёт объект-событие по имени типа и данным
     *
     * @param string $type
     * @param array $payload
     * @return EventInterface
     */
    protected function createEvent(string $type, array $payload): EventInterface
    {
        if (!isset($this->eventClassMap[$type])) {
            throw new \InvalidArgumentException("Unknown event type '{$type}'");
        }

        $eventClass = $this->eventClassMap[$type];

        // Предположим, что событие принимает данные в конструктор как именованные параметры
        return new $eventClass(...$payload);
    }

    /**
     * Вызывает у зарегистрированных хендлеров методы с событием
     *
     * @param EventInterface $event
     * @param ConnectionInterface|null $conn
     */
    public function dispatch(EventInterface $event, ?ConnectionInterface $conn = null): void
    {
        $eventClass = get_class($event);

        if (empty($this->handlers[$eventClass])) {
            throw new \RuntimeException("No handlers registered for event: {$eventClass}");
        }

        foreach ($this->handlers[$eventClass] as [$handler, $method]) {
            // Если у тебя есть логика передачи $conn — можно добавить в параметры
            $handler->$method($event);
        }
    }
}
