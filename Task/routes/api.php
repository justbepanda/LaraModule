<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Middleware\AuthMiddleware;

Route::middleware(AuthMiddleware::class)->group(function () {
    Route::prefix('v1')->name('v1.')->group(__DIR__ . '/v1/api.php');
});
