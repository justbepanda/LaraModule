<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\CreateTaskController;
use Modules\Task\Http\Controllers\GetTasksController;
use Modules\Task\Http\Controllers\GetTaskController;
use Modules\Task\Http\Controllers\GetTaskStatusesController;
use Modules\Task\Http\Controllers\UpdateTaskController;
use Modules\Task\Http\Controllers\GetTaskTypesController;
use Modules\Task\Http\Controllers\UploadTaskDocumentController;
use Modules\Task\Http\Controllers\UploadTaskPhotoController;

Route::prefix('tasks')->group(function () {
    Route::get('', GetTasksController::class);
    Route::post('', CreateTaskController::class);
    Route::prefix('{task}')->group(function () {
        Route::get('', GetTaskController::class);
        Route::put('', UpdateTaskController::class);

        Route::prefix('documents')->group(function () {
            Route::post('', UploadTaskDocumentController::class);
        });

        Route::prefix('photos')->group(function () {
            Route::post('', UploadTaskPhotoController::class);
        });
    });
});

Route::get('task-types', GetTaskTypesController::class);
Route::get('task-statuses', GetTaskStatusesController::class);
