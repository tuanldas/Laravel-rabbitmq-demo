<?php

namespace App\Domain\Services;

interface MessageQueueServiceInterface
{
    public function connect(string $host, int $port, string $user, string $password);

    public function consumer(string $queue, $callback);

    public function close();
}
