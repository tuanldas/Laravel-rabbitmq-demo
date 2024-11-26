<?php

namespace App\Console\Commands;

use App\Domain\Services\MessageQueueServiceInterface;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConsumer extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ';

    public function handle(
        MessageQueueServiceInterface $messageQueueService
    )
    {
        $messageQueueService->connect('rabbitmq', 5672, 'user', 'password');
        try {
            $messageQueueService->consumer('hello', function ($msg) {
                $data = json_decode($msg->body);
                var_dump($data);
            });
            $messageQueueService->close();
        } catch (\Exception $e) {
            $messageQueueService->close();
        }
    }

}
