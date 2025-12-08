<?php

use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\EventController;
use Illuminate\Support\Facades\Route;

// API routes for OrganizerAI modules — basic CRUD + helper endpoints
// Protect API v1 routes with Sanctum auth (or the configured API guard)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Custom note routes BEFORE apiResource to have precedence
    Route::post('notes/{note}/ai/suggest', [NoteController::class, 'suggest']);
    Route::post('notes/{note}/ai/analyze', [NoteController::class, 'analyze']);
    Route::post('notes/{note}/ai/expand', [NoteController::class, 'expand']);
    Route::post('notes/{note}/ai/summarize-content', [NoteController::class, 'summarizeContent']);
    Route::post('notes/{note}/to-event', [NoteController::class, 'toEvent']);
    Route::post('notes/{note}/to-task', [NoteController::class, 'toTask']);
    // Raw content AI endpoints for create-note flow
    Route::post('notes/ai/suggest', [NoteController::class, 'suggestRaw']);
    Route::post('notes/ai/analyze', [NoteController::class, 'analyzeRaw']);
    Route::post('notes/ai/expand', [NoteController::class, 'expandRaw']);
    Route::post('notes/ai/summarize', [NoteController::class, 'summarizeRaw']);
    Route::post('notes/{note}/summarize', [NoteController::class, 'summarize']);
    Route::post('notes/transform', [NoteController::class, 'transform']);
    
    // Standard CRUD routes
    Route::apiResource('notes', NoteController::class);

    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);

    Route::apiResource('events', EventController::class);
});
