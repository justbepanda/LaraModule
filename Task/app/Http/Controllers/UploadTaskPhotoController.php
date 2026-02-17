<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\Json\JResponse;
use Illuminate\Http\JsonResponse;
use Modules\Task\Http\Requests\UploadTaskPhotoRequest;
use Modules\Task\Models\TaskPhoto;
use Modules\Task\Services\TaskPhotoService;
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\TaskPhotoResource;
use Throwable;

/**
 * @OA\Post(
 *     path="/api/v1/tasks/{task_id}/photos",
 *     summary="Загрузить фотографии для задачи",
 *     description="
 *     Доступ:
 *     - Суперадминистратор – к любой задаче
 *     - Администратор – только к задачам своей компании
 *     - Пользователь – только если автор задачи",
 *     tags={"Tasks"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="task_id",
 *         in="path",
 *         required=true,
 *         description="ID задачи",
 *         @OA\Schema(type="string", format="uuid", example="31d9d7e0-8a14-43c9-97a3-8adcfb4b2e22")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="photos[]",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary", description="Файл фото"),
 *                     description="Массив загружаемых файлов. Возможные форматы: jpg,jpeg,png,webp,heic,heif"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Фотографии успешно загружены",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Успешно"),
 *             @OA\Property(property="request_id", type="string", format="uuid"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="string", format="uuid", example="019a9a17-a737-7135-87ec-6abb756c90b2"),
 *                     @OA\Property(property="task_id", type="string", format="uuid", example="019a904a-4e17-736f-a974-9247ad385489"),
 *                     @OA\Property(property="name", type="string", example="eduard.jpg"),
 *                     @OA\Property(property="url", type="string", example="https://client-service/storage/tasks/uuid-task/photos/file-name.pdf"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-26 09:55:35")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Неавторизован"),
 *     @OA\Response(response=422, description="Ошибка валидации")
 * )
 */
class UploadTaskPhotoController extends Controller
{
    /**
     * @param TaskPhotoService $taskPhotoService
     * @param TaskService $taskService
     */
    public function __construct(
        private readonly TaskPhotoService $taskPhotoService,
        private readonly TaskService $taskService,
    )
    {
    }

    /**
     * Загружает фотографии для задачи.
     *
     * @param string $taskId
     * @param UploadTaskPhotoRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(string $taskId, UploadTaskPhotoRequest $request): JsonResponse
    {
        $task = $this->taskService->getById($taskId);

        $this->authorizeResourceCreate(TaskPhoto::class, [$task]);

        $photos = $this->taskPhotoService->uploadPhotos($task, $request->toDto());

        return JResponse::new(
            TaskPhotoResource::collection($photos)
        )->created();
    }
}