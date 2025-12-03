<?php

use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\EventController;
use Illuminate\Support\Facades\Route;

// API routes for OrganizerAI modules — basic CRUD + helper endpoints
Route::prefix('v1')->group(function () {
    Route::apiResource('notes', NoteController::class);
    Route::post('notes/{note}/summarize', [NoteController::class, 'summarize']);
    Route::post('notes/transform', [NoteController::class, 'transform']);
    Route::post('notes/{note}/to-task', [NoteController::class, 'toTask']);

    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);

    Route::apiResource('events', EventController::class);
});
