<?php

namespace App\Providers;

use App\Domain\Services\MessageQueueServiceInterface;
use App\ExternalServices\RabbitMQService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            MessageQueueServiceInterface::class,
            RabbitMQService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
