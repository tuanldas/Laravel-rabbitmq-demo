<?php

namespace App\Console\Commands;

use App\Domain\Services\MessageQueueServiceInterface;
use App\Models\BreakingTime;
use App\Models\QueueLog;
use Illuminate\Console\Command;

class AcceptBreakingTime extends Command
{
    protected $signature = 'queue-attendance:accept-breaking-time';

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
        $messageQueueService->consumer('mattermost.attendance.user_accept_breaking_time', function ($msg) {
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
        $queueLog->queue = 'mattermost.attendance.user_accept_breaking_time';
        $queueLog->message = $msg->body;
        $queueLog->save();
        $data = json_decode($msg->body);
        $requestBreakingTime = BreakingTime::where('breaking_time_id', $data->ticket_id)->first();
        if ($requestBreakingTime) {
            $checkin = new \App\Models\AcceptBreakingTime();
            $checkin->team_id = $data->team_id;
            $checkin->channel_id = $data->channel_id;
            $checkin->ticket_id = $data->ticket_id;
            $checkin->create_at = $this->convertUnixToDateTimeGetMillisecond($data->create_at);
            $checkin->user_id = $data->user_id;
            $checkin->username = $data->username;
            $checkin->first_name = $data->first_name;
            $checkin->last_name = $data->last_name;
            $checkin->email = $data->email;
            $checkin->save();
            $requestBreakingTime->status = 'accepted';
            $requestBreakingTime->save();
        }
    }

    private function convertUnixToDateTimeGetMillisecond($unixTime): string
    {
        $timestampSeconds = floor($unixTime / 1000);
        $milliseconds = $unixTime % 1000;
        return date('Y-m-d H:i:s', $timestampSeconds) . '.' . sprintf('%03d', $milliseconds);
    }
}
