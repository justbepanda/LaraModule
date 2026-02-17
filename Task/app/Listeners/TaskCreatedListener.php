<?php

namespace Modules\Task\Listeners;

use Modules\Task\Events\TaskCreatedEvent;
use Modules\Task\Jobs\SendLeadToConnectorJob;

/**
 * Задача создалась.
 * Прослушка.
 */
class TaskCreatedListener
{
    /**
     * Handle the event.
     */
    public function handle(TaskCreatedEvent $event): void
    {
        SendLeadToConnectorJob::dispatch($event->task)
            ->delay(now()->addMinutes(1)); // Чтобы фотки и доки подкрепились к задаче
    }
}