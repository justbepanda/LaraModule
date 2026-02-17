<?php

namespace Modules\Task\Jobs;

use App\Enums\QueueType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Modules\Task\Models\Task;
use Modules\Connector\Services\ConnectorClientService;

/**
 * Отправки заявки в Connector.
 */
class SendLeadToConnectorJob implements ShouldQueue
{
    use Queueable;

    private const int GROUP_ID = 27;  // Группа распределения
    public int $tries = 5;
    public int $backoff = 60;

    protected Task $task;

    /**
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->onQueue(QueueType::COLLECTOR->value);
    }

    /**
     * @param ConnectorClientService $connectorClient
     * @return void
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle(ConnectorClientService $connectorClient): void
    {
        $this->task->load([
            'documents', 'photos', 'company', 'taskType', 'technique', 'author'
        ]);

        $payload = $this->buildLeadPayload();

        $connectorClient->sendLead($payload);
    }

    /**
     * Сбор данных из модели Task в формат, требуемый Коннектором.
     */
    private function buildLeadPayload(): array
    {
        $task = $this->task;

        return [
            "group_id" => self::GROUP_ID,
            "name" => $task->taskType?->name ?? '',

            "company" => [
                "name" => $task->company?->name ?? '',
                "inn" => $task->company?->inn ?? '',
                "phone" => $task->company?->contact_person_phone ?? '',
            ],

            "contact" => [
                "name" => $task->author?->fio ?? '',
                "phone" => $task->author?->phone ?? '',
            ],

            "technique" => [
                "type" => $task->technique?->category?->name ?? '',
                "vin" => $task->technique?->vin ?? '',
                "brand" => $task->technique?->brand ?? '',
                "model" => $task->technique?->model ?? '',

                "mileage" => [
                    "value" => $task->technique->mileage_value ?? 0,
                    "type" => $task->technique->mileage_type?->value ?? '',
                ]
            ],

            "address" => $task->address ?? '',
            "note" => $task->description ?? '',
            "tags" => [],
            "files" => $this->collectFileUrls(),
        ];
    }

    /**
     * Сбор ссылок на файлы.
     *
     * @return array
     */
    private function collectFileUrls(): array
    {
        $fileUrls = [];

        $paths = collect($this->task->photos ?? [])
            ->merge($this->task->documents ?? [])
            ->pluck('path')
            ->filter();

        foreach ($paths as $path) {
            $fileUrls[] = asset("storage/{$path}");
        }

        return $fileUrls;
    }

}