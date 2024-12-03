<?php

namespace App\Console\Commands;

use App\Domain\Services\MessageQueueServiceInterface;
use App\Models\BreakingTime;
use App\Models\QueueLog;
use Illuminate\Console\Command;

class RequestBreakingTime extends Command
{
    protected $signature = 'queue-attendance:request-breaking-time';

    protected $description = 'Command description';

    public function handle(
        MessageQueueServiceInterface $messageQueueService
    )
    {
        $messageQueueService->connect(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD'),
            env('RABBITMQ_VHOST')
        );
        $messageQueueService->consumer(env('QUEUE_REQUEST_BREAKING_TIME'), function ($msg) {
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
    }

    private function handleQueue($msg): void
    {
        $queueLog = new QueueLog();
        $queueLog->action = 'consume';
        $queueLog->queue = env('QUEUE_REQUEST_BREAKING_TIME');
        $queueLog->message = $msg->body;
        $queueLog->save();
        $data = json_decode($msg->body);
        $checkin = new BreakingTime();
        $checkin->create_at = $this->convertUnixToDateTimeGetMillisecond($data->create_at);
        $checkin->breaking_time_id = $data->id;
        $checkin->user_id = $data->user_id;
        $checkin->username = $data->username;
        $checkin->first_name = $data->first_name;
        $checkin->last_name = $data->last_name;
        $checkin->email = $data->email;
        $checkin->reason = $data->reason;
        $checkin->start_date = $data->start_date;
        $checkin->start_time = $data->start_time;
        $checkin->end_date = $data->end_date;
        $checkin->end_time = $data->end_time;
        $checkin->manager_id = $data->manager_id;
        $checkin->manager_username = $data->manager_username;
        $checkin->manager_email = $data->manager_email;
        $checkin->hr_id = $data->hr_id;
        $checkin->hr_username = $data->hr_username;
        $checkin->hr_email = $data->hr_email;
        $checkin->save();
    }

    private function convertUnixToDateTimeGetMillisecond($unixTime): string
    {
        $timestampSeconds = floor($unixTime / 1000);
        $milliseconds = $unixTime % 1000;
        return date('Y-m-d H:i:s', $timestampSeconds) . '.' . sprintf('%03d', $milliseconds);
    }
}
