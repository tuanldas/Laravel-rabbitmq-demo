<?php

namespace App\Console\Commands;

use App\Domain\Services\MessageQueueServiceInterface;
use App\Models\Checkin;
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
                $checkin = new Checkin();
                $checkin->user_id = $data->id;
                $checkin->username = $data->username;
                $checkin->create_at = date('Y-m-d H:i:s', $data->create_at);
                $checkin->first_name = $data->first_name;
                $checkin->last_name = $data->last_name;
                $checkin->email = $data->email;
                $checkin->save();
            });
            $messageQueueService->close();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $messageQueueService->close();
        }
    }
}
