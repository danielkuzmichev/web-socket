<?php

namespace App\Domain\Session\Entity;

use DateTime;
use Exception;
use JsonException;

class Session
{
    public function __construct(
        protected string $id,
        protected string $processId,
        protected ?DateTime $startAt,
        protected ?DateTime $endAt,
        protected int $countOfConnections,
        protected array $connections = [],
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function setProcessId(string $processId): self
    {
        $this->processId = $processId;

        return $this;
    }

    public function getStartAt(): ?DateTime
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTime $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTime
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTime $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getCountOfConnections(): int
    {
        return $this->countOfConnections;
    }

    public function setCountOfConnections(int $countOfConnections): self
    {
        $this->countOfConnections = $countOfConnections;

        return $this;
    }

    public function getConnections(): array
    {
        return $this->connections;
    }

    public function setConnections(array $connections): self
    {
        $this->connections = $connections;

        return $this;
    }

    public function removeConnection($connectionId): self
    {
        unset($this->connections[$connectionId]);

        return $this;
    }

    public function addConnection($connection): self
    {
        $this->connections[$connection] = $connection;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'processId' => $this->processId,
            'startAt' => $this->startAt?->format('Y-m-d H:i:s'),
            'endAt' => $this->endAt?->format('Y-m-d H:i:s'),
            'countOfConnections' => $this->countOfConnections,
            'connections' => $this->connections
        ];
    }

    /**
     * @throws JsonException
     */
    public function toJson(int $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * @throws Exception
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['processId'] ?? '',
            isset($data['startAt']) ? new DateTime($data['startAt']) : null,
            isset($data['endAt']) ? new DateTime($data['endAt']) : null,
            $data['countOfConnections'] ?? 0,
            $data['connections'] ?? []
        );
    }
}