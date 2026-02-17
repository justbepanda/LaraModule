<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\Json\JResponse;
use Illuminate\Http\JsonResponse;
use Modules\Task\Http\Requests\GetTasksRequest;
use Modules\Task\Models\Task;
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\TaskResource;

/**
 * Получение списка задач
 *
 * @OA\Get(
 *     path="/api/v1/tasks",
 *     summary="Получить список задач",
 *     description="
 *  Возвращает список задач компании. *
 *  **Доступность по ролям:**
 *  - SUPER_ADMIN — может получить задачи любой компании
 *  - ADMIN — только задачи своей компании
 *  - USER — только задачи своей компании",
 *     tags={"Tasks"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="filters[company_id]",
 *         in="query",
 *         required=false,
 *         description="ID компании (UUID)",
 *         @OA\Schema(type="string", format="uuid", example="c1a3f9e8-1234-4567-890a-bcdef1234567")
 *     ),
 *     @OA\Parameter(
 *         name="filters[description]",
 *         in="query",
 *         required=false,
 *         description="Фильтр по описанию задачи",
 *         @OA\Schema(type="string", example="ремонт двигателя")
 *     ),
 *     @OA\Parameter(
 *         name="filters[task_type_id]",
 *         in="query",
 *         required=false,
 *         description="Фильтр по типу задачи (UUID)",
 *         @OA\Schema(type="string", format="uuid", example="e1d2c3b4-5678-90ab-cdef-1234567890ab")
 *     ),
 *     @OA\Parameter(
 *         name="filters[vin]",
 *         in="query",
 *         required=false,
 *         description="Фильтр по VIN технике",
 *         @OA\Schema(type="string", example="XTA210800L1234567")
 *     ),
 *     @OA\Parameter(
 *         name="filters[registration_number]",
 *         in="query",
 *         required=false,
 *         description="Фильтр по регистрационному номеру техники",
 *         @OA\Schema(type="string", example="А123ВС60")
 *     ),
 *     @OA\Parameter(
 *         name="filters[value]",
 *         in="query",
 *         required=false,
 *         description="Поиск по значению (универсальный фильтр)",
 *         @OA\Schema(type="string", example="сцепление")
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Номер страницы пагинации",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Количество элементов на страницу",
 *         @OA\Schema(type="integer", example=15)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Список задач успешно получен",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Успешно"),
 *             @OA\Property(property="request_id", type="string", example="abc-123"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="items",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="string", format="uuid", example="019a9a12-e753-73ec-a6ca-a5e0a3b68579"),
 *                         @OA\Property(
 *                             property="company",
 *                             type="object",
 *                             @OA\Property(property="id", type="string", format="uuid", example="019a904a-4e17-736f-a974-9247ad385489"),
 *                             @OA\Property(property="name", type="string", example="ООО Рога и копыта"),
 *                             @OA\Property(property="inn", type="integer", example=1234567890),
 *                             @OA\Property(property="contact_person_fio", type="string", example="Иванов Иван Иванович"),
 *                             @OA\Property(property="contact_person_phone", type="string", example="+79991234567")
 *                         ),
 *                         @OA\Property(
 *                             property="task_type",
 *                             type="object",
 *                             @OA\Property(property="id", type="string", format="uuid", example="8a648ab8-c161-11f0-abe4-0242ac150002"),
 *                             @OA\Property(property="name", type="string", example="ТО")
 *                         ),
 *                         @OA\Property(
 *                             property="task_status",
 *                             type="object",
 *                             @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                             @OA\Property(property="name", type="string", example="Готово")
 *                         ),
 *                         @OA\Property(
 *                             property="technique",
 *                             type="object",
 *                             @OA\Property(property="id", type="string", format="uuid", example="019a974b-5fc3-72de-b68a-2e25829a5071"),
 *                             @OA\Property(property="name", type="string", example="Toyota Camry"),
 *                             @OA\Property(property="vin", type="string", example="1xxxx23"),
 *                             @OA\Property(property="registration_number", type="string", example="А123ВС77"),
 *                             @OA\Property(property="technique_brand", type="object",
 *                                 @OA\Property(property="id", type="string", example="8a3fcc98-c161-11f0-abe4-0242ac150002"),
 *                                 @OA\Property(property="name", type="string", example="Toyota")
 *                             ),
 *                             @OA\Property(property="model", type="string", example="Camry"),
 *                             @OA\Property(
 *                                 property="mileage",
 *                                 type="object",
 *                                 @OA\Property(property="value", type="number", example=120000),
 *                                 @OA\Property(property="type", type="string", example="kilometers")
 *                             )
 *                         ),
 *                         @OA\Property(property="address", type="string", example="г. Псков, ул. Ленина, 111111111111111111"),
 *                         @OA\Property(
 *                             property="mileage",
 *                             type="object",
 *                             @OA\Property(property="value", type="string", example="1500.00"),
 *                             @OA\Property(property="type", type="string", example="kilometers")
 *                         ),
 *                         @OA\Property(property="description", type="string", example="Обновлённая задача по обслуживанию"),
 *                         @OA\Property(property="documents", type="array", @OA\Items(type="object")),
 *                         @OA\Property(property="photos", type="array", @OA\Items(type="object")),
 *                         @OA\Property(property="author_id", type="string", format="uuid", example="b6a1f2c4-8e23-4f1a-92d8-3b9c7e8d4a21"),
 *                         @OA\Property(property="created_at", type="string", example="2025-11-19 03:05:21"),
 *                         @OA\Property(property="updated_at", type="string", example="2025-11-19 03:05:21"),
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="paginate",
 *                     type="object",
 *                     @OA\Property(property="per_page", type="integer", example=10),
 *                     @OA\Property(property="page", type="integer", example=1),
 *                     @OA\Property(property="count", type="integer", example=3),
 *                     @OA\Property(property="pages", type="integer", example=1)
 *                 )
 *             )
 *         )
 *     ),
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
class GetTasksController extends Controller
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
     * @param GetTasksRequest $request
     * @return JsonResponse
     */
    public function __invoke(GetTasksRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->authorizeResourceIndex(Task::class, [$user->company]);

        return JResponse::new(TaskResource::toPaginateCollection(
            $this->taskService->get($request->toDto())
        ))->success();
    }

}