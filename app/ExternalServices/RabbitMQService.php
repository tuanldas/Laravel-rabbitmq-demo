<?php

namespace App\ExternalServices;

use App\Adapters\RabbitMQ\RabbitMQConnection;
use App\Domain\Services\MessageQueueServiceInterface;

class RabbitMQService implements MessageQueueServiceInterface
{
    private $rabbitMQConnection;

    /**
     * @throws \Exception
     */
    public function connect(string $host, int $port, string $user, string $password, string $vhost = '/'): void
    {
        $this->rabbitMQConnection = new RabbitMQConnection($host, $port, $user, $password, $vhost);
    }

    public function consumer(string $queue, $callback): void
    {
        $channel = $this->rabbitMQConnection->getChannel();
        $channel->queue_declare($queue, false, true, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    public function close()
    {
        $this->rabbitMQConnection->close();
    }

}
