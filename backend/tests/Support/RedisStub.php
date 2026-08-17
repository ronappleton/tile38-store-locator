<?php

declare(strict_types=1);

namespace Tests\Support;

use Redis;

/**
 * In-memory stand-in for phpredis. Records every command sent by the client
 * and returns a canned response, so the service layer can be tested without a
 * Tile38 server.
 */
class RedisStub extends Redis
{
    /**
     * @var array<int, array<int, mixed>>
     */
    public array $recordedCommands = [];

    public mixed $response = true;

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function connect(...$arguments): bool
    {
        return true;
    }

    public function auth(mixed $credentials): Redis|bool
    {
        return true;
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    public function rawCommand(string $command, mixed ...$arguments): mixed
    {
        $this->recordedCommands[] = [$command, ...$arguments];

        return $this->response;
    }
}
