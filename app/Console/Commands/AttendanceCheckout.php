<?php

namespace App\Console\Commands;

use App\Domain\Services\MessageQueueServiceInterface;
use App\Models\Checkout;
use App\Models\QueueLog;
use Illuminate\Console\Command;

class AttendanceCheckout extends Command
{
    protected $signature = 'queue-attendance:checkout';
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
            $messageQueueService->consumer('mattermost.attendance.user_checkout', function ($msg) {
                $maxRetries = 3;
                $retryCount = 0;

                while ($retryCount < $maxRetries) {
                    try {
                        $this->info('Received message: ' . $msg->body);
                        $this->handleQueue($msg);
                        break;
                    } catch (\Exception $e) {
                        $retryCount++;
                        echo " [!] Error: {$e->getMessage()}, retrying {$retryCount}/{$maxRetries}...\n";
                        sleep(2);
                    }
                }

                if ($retryCount >= $maxRetries) {
                    echo " [x] Failed to process message after {$maxRetries} retries.\n";
                    $msg->nack(false, false);
                } else {
                    $msg->ack();
                }
            });
            $messageQueueService->close();
        } catch (\Exception $e) {
            var_dump(' [x] Error:', $e->getMessage());
            $messageQueueService->close();
        }
    }

    private function handleQueue($msg): void
    {
        $queueLog = new QueueLog();
        $queueLog->action = 'consume';
        $queueLog->queue = 'mattermost.attendance.user_checkout';
        $queueLog->message = $msg->body;
        $queueLog->save();
        $data = json_decode($msg->body);
        $checkin = new Checkout();
        $checkin->user_id = $data->id;
        $checkin->username = $data->username;
        $checkin->create_at = date('Y-m-d H:i:s', $data->create_at);
        $checkin->first_name = $data->first_name;
        $checkin->last_name = $data->last_name;
        $checkin->email = $data->email;
        $checkin->save();
    }
}
