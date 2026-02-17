<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\Json\JResponse;
use Illuminate\Http\JsonResponse;
use Modules\Task\Http\Requests\UpdateTaskRequest;
use Modules\Task\Models\Task;
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\TaskResource;

/**
 * Обновление задачи
 *
 * @OA\Put(
 *     path="/api/v1/tasks/{task_id}",
 *     summary="Обновить задачу",
 *     description="
 *  Доступ:
 *    - Суперадминистратор – любые задачи.
 *    - Администратор – задачи своей компании.
 *    - Пользователь – только созданные им задачи.
 *  ",
 *     tags={"Tasks"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="task_id",
 *         in="path",
 *         required=true,
 *         description="ID задачи",
 *         @OA\Schema(type="string", format="uuid", example="31d9d7e0-8a14-43c9-97a3-8adcfb4b2e22")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"task_type_id","technique_id","mileage", "company_id"},
 *             @OA\Property(property="company_id", type="string", format="uuid", example="019a904a-4e17-736f-a974-9247ad385489"),
 *             @OA\Property(property="task_type_id", type="string", format="uuid", example="8a648ab8-c161-11f0-abe4-0242ac150002"),
 *             @OA\Property(property="technique_id", type="string", format="uuid", example="019a974b-5fc3-72de-b68a-2e25829a5071"),
 *             @OA\Property(property="address", type="string", example="г. Псков, ул. Ленина, 111111111111111111"),
 *             @OA\Property(
 *                 property="mileage",
 *                 type="object",
 *                 @OA\Property(property="value", type="number", format="float", example=1500),
 *                 @OA\Property(property="type", type="string", enum={"kilometers","hours"}, example="kilometers")
 *             ),
 *             @OA\Property(property="description", type="string", example="Обновлённая задача по обслуживанию"),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Задача успешно обновлена",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Успешно"),
 *             @OA\Property(property="request_id", type="string", format="uuid", example="b5d75392-8395-454f-ab8b-89b6289cd3c1"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="string", format="uuid", example="019a9a12-e753-73ec-a6ca-a5e0a3b68579"),
 *                 @OA\Property(
 *                     property="company",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid", example="019a904a-4e17-736f-a974-9247ad385489"),
 *                     @OA\Property(property="name", type="string", example="ООО Рога и копыта"),
 *                     @OA\Property(property="inn", type="integer", example=1234567890),
 *                     @OA\Property(property="contact_person_fio", type="string", example="Иванов Иван Иванович"),
 *                     @OA\Property(property="contact_person_phone", type="string", example="+79991234567")
 *                 ),
 *                 @OA\Property(
 *                     property="task_type",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid", example="8a648ab8-c161-11f0-abe4-0242ac150002"),
 *                     @OA\Property(property="name", type="string", example="ТО")
 *                 ),
 *                 @OA\Property(
 *                      property="task_status",
 *                      type="object",
 *                      @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                      @OA\Property(property="name", type="string", example="Готово")
 *                 ),
 *                 @OA\Property(
 *                     property="technique",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid", example="019a974b-5fc3-72de-b68a-2e25829a5071"),
 *                     @OA\Property(property="name", type="string", example="Toyota Camry"),
 *                     @OA\Property(property="vin", type="string", example="1xxxx23"),
 *                     @OA\Property(property="registration_number", type="string", example="А123ВС77"),
 *                     @OA\Property(property="technique_brand", type="object",
 *                         @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                         @OA\Property(property="name", type="string", example="Toyota")
 *                     ),
 *                     @OA\Property(property="model", type="string", example="Camry"),
 *                     @OA\Property(
 *                         property="mileage",
 *                         type="object",
 *                         @OA\Property(property="value", type="number", example=120000),
 *                         @OA\Property(property="type", type="string", example="kilometers")
 *                     )
 *                 ),
 *                 @OA\Property(property="address", type="string", example="г. Псков, ул. Ленина, 111111111111111111"),
 *                 @OA\Property(
 *                     property="mileage",
 *                     type="object",
 *                     @OA\Property(property="value", type="number", example=1500),
 *                     @OA\Property(property="type", type="string", example="kilometers")
 *                 ),
 *                 @OA\Property(property="description", type="string", example="Обновлённая задача по обслуживанию"),
 *                 @OA\Property(property="documents", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="photos", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="author_id", type="string", format="uuid", example="b6a1f2c4-8e23-4f1a-92d8-3b9c7e8d4a21"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-19 03:05:21"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-19 03:37:06"),
 *             )
 *         )
 *     ),
 *     @OA\Response(response=400, description="Неверный запрос"),
 *     @OA\Response(response=401, description="Неавторизован"),
 *     @OA\Response(response=403, description="Нет доступа"),
 *     @OA\Response(response=404, description="Задача не найдена"),
 *     @OA\Response(response=422, description="Ошибка валидации"),
 *     @OA\Response(response=500, description="Внутренняя ошибка сервера")
 * )
 */
class UpdateTaskController extends Controller
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
     * @param string $taskId
     * @param UpdateTaskRequest $request
     * @return JsonResponse
     */
    public function __invoke(string $taskId, UpdateTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->getById($taskId);
        $this->authorizeResourceUpdate(Task::class, [$task]);

        return JResponse::new(new TaskResource(
            $this->taskService->update($task, $request->toDto())
        ))->success();
    }
}
