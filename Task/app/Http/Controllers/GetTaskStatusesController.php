<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\Json\JResponse;
use Illuminate\Http\JsonResponse;
use Modules\Task\Services\TaskStatusService;
use Modules\Task\Transformers\TaskStatusCollection;

/**
 * Статусы задач.
 * Получение списка
 *
 * @OA\Get(
 *     path="/api/v1/task-statuses",
 *     summary="Получение списка статусов задач",
 *     description="Возвращает все статусы задач. Параметры не передаются. Доступно всем пользователям",
 *     security={{"bearerAuth":{}}},
 *     tags={"TaskStatuses"},
 *     @OA\Response(
 *         response=200,
 *         description="Успешно",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Успешно"),
 *             @OA\Property(property="request_id", type="string", example="52f8c607-cf1a-41b5-8497-343b681e16e3"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="items",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="id", type="string", example="dec65058-bf86-11f0-b635-0242ac150002"),
 *                         @OA\Property(property="name", type="string", example="Готово")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *          response=401,
 *          description="Неавторизован",
 *          @OA\JsonContent(
 *              type="object",
 *              @OA\Property(property="message", type="string", example="Неавторизован"),
 *              @OA\Property(property="request_id", type="string", example="52f8c607-cf1a-41b5-8497-343b681e16e3")
 *          )
 *      ),
 *     @OA\Response(
 *         response=500,
 *         description="Внутренняя ошибка сервера",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Что-то пошло не так"),
 *             @OA\Property(property="request_id", type="string", example="52f8c607-cf1a-41b5-8497-343b681e16e3")
 *         )
 *     )
 * )
 */
class GetTaskStatusesController extends Controller
{
    /**
     * @param TaskStatusService $taskStatusService
     */
    public function __construct(
        private readonly TaskStatusService $taskStatusService,
    )
    {
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $taskStatuses = $this->taskStatusService->getAll();
        return JResponse::new(new TaskStatusCollection($taskStatuses))->success();
    }
}