<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\Json\JResponse;
use Illuminate\Http\JsonResponse;
use Modules\Company\Models\Company;
use Modules\Task\Http\Requests\CreateTaskRequest;
use Modules\Task\Models\Task;
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\TaskResource;

/**
 * @OA\Post(
 *     path="/api/v1/tasks",
 *     summary="Создать задачу",
 *     description="
 *  Создаёт новую задачу, связанную с техникой конкретной компании.
 *
 *   **Доступность по ролям:**
 * - SUPER_ADMIN — может создавать задачи для любой компании.
 * - ADMIN — может создавать задачи только в своей компании.
 * - USER — может создавать задачи только в своей компании.",
 *     tags={"Tasks"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"task_type_id","technique_id","mileage", "company_id"},
 *
 *             @OA\Property(
 *                 property="company_id",
 *                 type="string",
 *                 format="uuid",
 *                 description="UUID компании",
 *                 example="8a3fcc98-c161-11f0-abe4-0242ac150002"
 *             ),
 *
 *             @OA\Property(
 *                 property="task_type_id",
 *                 type="string",
 *                 format="uuid",
 *                 description="UUID типа задачи",
 *                 example="8a3fcc98-c161-11f0-abe4-0242ac150002"
 *             ),
 *
 *             @OA\Property(
 *                 property="technique_id",
 *                 type="string",
 *                 format="uuid",
 *                 description="UUID техники",
 *                 example="019a974b-5fc3-72de-b68a-2e25829a5071"
 *             ),
 *
 *             @OA\Property(
 *                 property="address",
 *                 type="string",
 *                 nullable=true,
 *                 example="г. Псков, ул. Ленина, 11"
 *             ),
 *
 *             @OA\Property(
 *                 property="mileage",
 *                 type="object",
 *                 required={"type"},
 *                 @OA\Property(property="value", type="number", nullable=true, example=1500),
 *                 @OA\Property(
 *                     property="type",
 *                     type="string",
 *                     enum={"kilometers","hours"},
 *                     example="kilometers"
 *                 )
 *             ),
 *
 *             @OA\Property(
 *                 property="description",
 *                 type="string",
 *                 nullable=true,
 *                 example="Задача по обслуживанию"
 *             ),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Задача успешно создана",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Успешно"),
 *             @OA\Property(property="request_id", type="string", format="uuid", example="c1a3f9e8-1234-4567-890a-bcdef1234567"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *
 *                 @OA\Property(property="id", type="string", example="019a9a17-a737-7135-87ec-6abb756c90b2"),
 *
 *                 @OA\Property(
 *                     property="company",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="019a904a-4e17-736f-a974-9247ad385489"),
 *                     @OA\Property(property="name", type="string", example="ООО Рога и копыта"),
 *                     @OA\Property(property="inn", type="integer", example=1234567890),
 *                     @OA\Property(property="contact_person_fio", type="string", example="Иванов Иван Иванович"),
 *                     @OA\Property(property="contact_person_phone", type="string", example="+79991234567"),
 *                 ),
 *
 *                 @OA\Property(
 *                     property="task_type",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                     @OA\Property(property="name", type="string", example="Ремонт")
 *                 ),
 *
 *                 @OA\Property(
 *                     property="task_status",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                     @OA\Property(property="name", type="string", example="Готово")
 *                 ),
 *
 *                 @OA\Property(
 *                     property="technique",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="019a974b-5fc3-72de-b68a-2e25829a5071"),
 *                     @OA\Property(property="name", type="string", example="Toyota Camry"),
 *                     @OA\Property(property="vin", type="string", example="1xxxx23"),
 *                     @OA\Property(property="registration_number", type="string", example="А123ВС77"),
 *                     @OA\Property(property="technique_brand", type="object",
 *                          @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                          @OA\Property(property="name", type="string", example="Toyota")
 *                     ),
 *                     @OA\Property(property="model", type="string", example="Camry"),
 *                     @OA\Property(
 *                         property="mileage",
 *                         type="object",
 *                         required={"type"},
 *                         @OA\Property(
 *                             property="value",
 *                             type="number",
 *                             nullable=true,
 *                             example=1500,
 *                             description="Значение пробега/наработки техники"
 *                         ),
 *                         @OA\Property(
 *                             property="type",
 *                             type="string",
 *                             enum={"kilometers","hours"},
 *                             example="kilometers",
 *                             description="Тип пробега/наработки"
 *                         )
 *                     )
 *                 ),
 *
 *                 @OA\Property(property="address", type="string", example="г. Псков, ул. Ленина, 11"),
 *
 *                 @OA\Property(
 *                     property="mileage",
 *                     type="object",
 *                     @OA\Property(property="value", type="number", example=1500),
 *                     @OA\Property(property="type", type="string", example="kilometers")
 *                 ),
 *
 *                 @OA\Property(property="description", type="string", example="Задача по обслуживанию"),
 *                 @OA\Property(property="author_id", type="string", format="uuid", example="b6a1f2c4-8e23-4f1a-92d8-3b9c7e8d4a21"),
 *                 @OA\Property(property="created_at", type="string", example="2025-11-19 03:10:32"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-11-19 03:10:32"),
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Неавторизован",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Неавторизован"),
 *             @OA\Property(property="request_id", type="string", example="abc-123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Нет доступа",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Отсутствует доступ"),
 *             @OA\Property(property="request_id", type="string", example="abc-123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Ошибка валидации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
 *             @OA\Property(property="request_id", type="string", example="abc-123"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 example={"task_type_id": "Поле обязательно для заполнения"}
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Внутренняя ошибка сервера",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Что-то пошло не так"),
 *             @OA\Property(property="request_id", type="string", example="abc-123")
 *         )
 *     )
 * )
 */
class CreateTaskController extends Controller
{
    /**
     * @param TaskService $taskService
     */
    public function __construct(
        private readonly TaskService $taskService,
    )
    {
    }

    /**
     * @param CreateTaskRequest $request
     * @return JsonResponse
     */
    public function __invoke(CreateTaskRequest $request): JsonResponse
    {
        $dto = $request->toDto();
        $company = Company::findOrFail($dto->companyId);

        $this->authorizeResourceCreate(Task::class, [$company]);

        return JResponse::new(
            new TaskResource($this->taskService->create($dto))
        )->created();
    }
}