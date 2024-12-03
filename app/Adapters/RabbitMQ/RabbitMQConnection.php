<?php

namespace App\Adapters\RabbitMQ;

use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConnection
{
    private AMQPStreamConnection $connection;
    private AbstractChannel|AMQPChannel $channel;

    /**
     * @throws \Exception
     */
    public function __construct(string $host, int $port, string $user, string $password, string $vhost = '/')
    {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
        $this->channel = $this->connection->channel();
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function getChannel(): AMQPChannel|AbstractChannel
    {
        return $this->channel;
    }
    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }
}
