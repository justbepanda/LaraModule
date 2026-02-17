<?php

namespace Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\Json\JResponse;
use Illuminate\Http\JsonResponse;
use Modules\Task\Http\Requests\UploadTaskDocumentRequest;
use Modules\Task\Models\TaskDocument;
use Modules\Task\Services\TaskDocumentService;
use Modules\Task\Services\TaskService;
use Modules\Task\Transformers\TaskDocumentResource;
use Throwable;

/**
 * @OA\Post(
 *     path="/api/v1/tasks/{task_id}/documents",
 *     summary="Загрузить документы для задачи",
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
 *                     property="documents[]",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary", description="Файл документа"),
 *                     description="Массив загружаемых файлов. Возможные форматы: pdf,doc,docx,xls,xlsx,txt,rtf,csv"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Документы успешно загружены",
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
 *                     @OA\Property(property="name", type="string", example="Договор_123.pdf"),
 *                     @OA\Property(property="url", type="string", example="https://client-service/storage/tasks/uuid-task/documents/file-name.pdf"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-26 09:55:35")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Неавторизован"),
 *     @OA\Response(response=422, description="Ошибка валидации")
 * )
 */
class UploadTaskDocumentController extends Controller
{
    /**
     * @param TaskDocumentService $taskDocumentService
     * @param TaskService $taskService
     */
    public function __construct(
        private readonly TaskDocumentService $taskDocumentService,
        private readonly TaskService $taskService,
    )
    {
    }

    /**
     * Загружает документы для задачи.
     *
     * @param string $taskId
     * @param UploadTaskDocumentRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(string $taskId, UploadTaskDocumentRequest $request): JsonResponse
    {
        $task = $this->taskService->getById($taskId);

        $this->authorizeResourceCreate(TaskDocument::class, [$task]);

        $documents = $this->taskDocumentService->uploadDocuments($task, $request->toDto());

        return JResponse::new(
            TaskDocumentResource::collection($documents)
        )->created();
    }
}