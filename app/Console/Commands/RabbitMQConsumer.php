<?php

namespace App\Console\Commands;

use App\Domain\Services\MessageQueueServiceInterface;
use Illuminate\Console\Command;

class RabbitMQConsumer extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ';

    public function handle(
        MessageQueueServiceInterface $messageQueueService
    )
    {
        $messageQueueService->connect(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD'),
            'cg.internal1'
        );
        try {
            $messageQueueService->consumer('mattermost.attendance.user_checkin', function ($msg) {
                $data = json_decode($msg->body);
                var_dump($data);
            });
            $messageQueueService->close();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $messageQueueService->close();
        }
    }
}
