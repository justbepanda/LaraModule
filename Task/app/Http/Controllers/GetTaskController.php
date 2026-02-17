<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Task\Models\Task;
use Illuminate\Http\JsonResponse;
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\TaskResource;
use App\Http\Responses\Json\JResponse;



/**
 * Получение задачи
 *
 * @OA\Get(
 *     path="/api/v1/tasks/{task_id}",
 *     summary="Получить задачу",
 *     description="
 *  Возвращает данные задачи по ID.
 *
 *  **Доступность по ролям:**
 *  - SUPER_ADMIN — может получить любую задачу любой компании
 *  - ADMIN — может получить только задачи своей компании
 *  - USER — может получить только задачи своей компании",
 *     tags={"Tasks"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="task_id",
 *         in="path",
 *         required=true,
 *         description="ID задачи (UUID)",
 *         @OA\Schema(type="string", format="uuid", example="c1a3f9e8-1234-4567-890a-bcdef1234567")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Задача успешно получена",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Успешно"),
 *             @OA\Property(property="request_id", type="string", format="uuid", example="b123f9e8-1234-4567-890a-bcdef1234567"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="string", format="uuid", example="019a9a12-e753-73ec-a6ca-a5e0a3b68579"),
 *                 @OA\Property(
 *                     property="company",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="019a904a-4e17-736f-a974-9247ad385489"),
 *                     @OA\Property(property="name", type="string", example="ООО Рога и копыта"),
 *                     @OA\Property(property="inn", type="integer", example=1234567890),
 *                     @OA\Property(property="contact_person_fio", type="string", example="Иванов Иван Иванович"),
 *                     @OA\Property(property="contact_person_phone", type="string", example="+79991234567")
 *                 ),
 *                 @OA\Property(
 *                     property="task_type",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                     @OA\Property(property="name", type="string", example="Ремонт")
 *                 ),
 *                 @OA\Property(
 *                     property="task_status",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                     @OA\Property(property="name", type="string", example="Готово")
 *                 ),
 *                 @OA\Property(
 *                     property="technique",
 *                     type="object",
 *                     @OA\Property(property="id", type="string", example="019a974b-5fc3-72de-b68a-2e25829a5071"),
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
 *                 @OA\Property(property="address", type="string", example="г. Псков, ул. Ленина, 11"),
 *                 @OA\Property(
 *                     property="mileage",
 *                     type="object",
 *                     @OA\Property(property="value", type="string", example="1500.00"),
 *                     @OA\Property(property="type", type="string", example="kilometers")
 *                 ),
 *                 @OA\Property(property="description", type="string", example="Задача по обслуживанию"),
 *                 @OA\Property(property="documents", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="photos", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="author_id", type="string", format="uuid", example="b6a1f2c4-8e23-4f1a-92d8-3b9c7e8d4a21"),
 *                 @OA\Property(property="created_at", type="string", example="2025-11-19 03:05:21"),
 *                 @OA\Property(property="updated_at", type="string", example="2025-11-19 03:05:21"),
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Неавторизован",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Неавторизован"),
 *             @OA\Property(property="request_id", type="string", example="abc-123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Нет доступа",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Отсутствует доступ"),
 *             @OA\Property(property="request_id", type="string", example="abc-123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Задача не найдена",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Api не найдено"),
 *             @OA\Property(property="request_id", type="string", example="abc-123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Внутренняя ошибка сервера",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Что-то пошло не так"),
 *             @OA\Property(property="request_id", type="string", example="abc-123")
 *         )
 *     )
 * )
 */
class GetTaskController extends Controller
{
    /**
     * @param TaskService $taskService
     */
    public function __construct(
        private TaskService $taskService,
    )
    {
    }

    /**
     * @param string $taskId
     * @return JsonResponse
     */
    public function __invoke(string $taskId): JsonResponse
    {
        $task = $this->taskService->getById($taskId);
        $this->authorizeResourceShow(Task::class, [$task]);
        return JResponse::new(
            new TaskResource($task)
        )->success();
    }
}