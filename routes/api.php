<?php

use App\Http\Controllers\Api\V1\AnalyticsApiController;
use App\Http\Controllers\Api\V1\BudgetApiController;
use App\Http\Controllers\Api\V1\CategoryApiController;
use App\Http\Controllers\Api\V1\DashboardApiController;
use App\Http\Controllers\Api\V1\SavingsApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/dashboard', [DashboardApiController::class, 'index']);
    
    Route::get('/transactions', [TransactionApiController::class, 'index']);
    Route::post('/transactions', [TransactionApiController::class, 'store']);
    Route::delete('/transactions/{transaction}', [TransactionApiController::class, 'destroy']);
    
    Route::get('/budgets', [BudgetApiController::class, 'index']);
    Route::post('/budgets', [BudgetApiController::class, 'storeOrUpdate']);

    Route::get('/categories', [CategoryApiController::class, 'index']);
    Route::post('/categories', [CategoryApiController::class, 'store']);
    Route::put('/categories/{category}', [CategoryApiController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryApiController::class, 'destroy']);

    Route::get('/savings', [SavingsApiController::class, 'show']);
    Route::post('/savings', [SavingsApiController::class, 'update']);

    Route::get('/analytics', [AnalyticsApiController::class, 'index']);
});

