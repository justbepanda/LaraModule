<?php

namespace Modules\Task\Tests\Unit\Jobs;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Modules\Company\Models\Company;
use Modules\Task\Models\TaskDocument;
use Modules\Task\Models\TaskPhoto;
use Modules\Task\Models\TaskType;
use Modules\Technique\Enums\TechniqueMileageType;
use Modules\Technique\Models\Technique;
use Modules\User\Models\User;
use Tests\TestCase;
use Modules\Task\Models\Task;
use Modules\Task\Jobs\SendLeadToConnectorJob;
use Modules\Connector\Services\ConnectorClientService;
use Mockery\MockInterface;

/**
 * Отправка заявки в Connector.
 * Тесты.
 */
class SendLeadToConnectorTest extends TestCase
{
    use WithFaker;

    private const int GROUP_ID = 27;

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function test_builds_payload_correctly_and_calls_service()
    {
        $task = Task::factory()
            ->hasPhotos(2)
            ->hasDocuments(3)
            ->create();

        $this->mock(ConnectorClientService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendLead')
                ->once()
                ->withArgs(function ($payload) {
                    if ($payload['group_id'] !== self::GROUP_ID) return false;
                    if (count($payload['files']) !== 5) return false;
                    if (!str_contains($payload['files'][0], 'http')) return false;
                    return true;
                });
        });

        $job = new SendLeadToConnectorJob($task);
        $job->handle(app(ConnectorClientService::class));
    }

    /**
     * Проверка, что заявка отправляется с верными полями.
     *
     * @return void
     * @throws ConnectionException
     * @throws RequestException
     */
    public function test_lead_is_sent_with_full_payload_successfully()
    {
        $user = User::factory()->superAdmin()->create();
        $technique = Technique::factory()->create();
        $company = Company::factory()->create();

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'task_type_id' => TaskType::factory(),
            'technique_id' => $technique->id,
            'address' => $this->faker->address,
            'mileage_value' => $this->faker->numberBetween(0, 5000),
            'mileage_type' => $this->faker->randomElement(TechniqueMileageType::cases())->value,
            'description' => $this->faker->sentence,
            'author_id' => $user->id,
        ]);

        $photos = TaskPhoto::factory()->count(2)->create(['task_id' => $task]);
        $documents = TaskDocument::factory()->count(2)->create(['task_id' => $task]);

        $expectedFileUrls = collect($documents)
            ->merge($photos)
            ->pluck('path')
            ->map(fn ($path) => asset("storage/{$path}"))
            ->values()
            ->all();

        $expectedPayload = [
            "group_id" => 27,
            "name" => $task->taskType->name,
            "company" => [
                "name" => $company->name,
                "inn" => $company->inn,
                "phone" => $company->contact_person_phone,
            ],
            "contact" => [
                "name" => $user->fio,
                "phone" => $user->phone,
            ],
            "technique" => [
                "type" => $technique->category?->name ?? '',
                "vin" => $technique->vin,
                "brand" => $technique->brand ?? '',
                "model" => $technique->model ?? '',
                "mileage" => [
                    "value" => $technique->mileage_value,
                    "type" => $technique->mileage_type->value,
                ],
            ],
            "address" => $task->address,
            "note" => $task->description,
            "tags" => [],
            "files" => $expectedFileUrls,
        ];

        $this->mock(ConnectorClientService::class, function (MockInterface $mock) use ($expectedPayload, $expectedFileUrls) {

            $mock->shouldReceive('sendLead')
                ->once()
                ->withArgs(function ($payload) use ($expectedPayload, $expectedFileUrls) {
                    $this->assertSame(
                        collect($expectedPayload)->except('files')->all(),
                        collect($payload)->except('files')->all()
                    );

                    // Файлы отдельно, а то кривая сортировка
                    $this->assertEqualsCanonicalizing(
                        $expectedFileUrls,
                        $payload['files']
                    );

                    return true;
                })
                ->andReturn(['status' => 'ok']);
        });

        new SendLeadToConnectorJob($task)
            ->handle(app(ConnectorClientService::class));
    }
}