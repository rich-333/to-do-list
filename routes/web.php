<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Models\Note;
use App\Models\Task;
use App\Models\Event;

Route::get('/', function () {
    $notas = Note::query()->latest()->take(8)->get();
    $tareas = Task::query()->latest()->take(8)->get();
    $eventos = Event::query()->latest()->take(8)->get();
    return view('organizer.index', compact('notas','tareas','eventos'));
});

Route::get('/users', [AuthController::class, 'obtenerUsuarios']);

// Minimal debug pages to exercise backend endpoints
Route::get('/organizer', function () {
    return view('organizer.index');
});

Route::get('/organizer/notes', function () {
    return view('organizer.notes');
});

Route::get('/organizer/tasks', function () {
    return view('organizer.tasks');
});

Route::get('/organizer/events', function () {
    return view('organizer.events');
});

// Spanish aliases (sin estilos) for quick debugging
Route::get('/organizer/notas', function () {
    return view('organizer.notes');
});

Route::get('/organizer/tareas', function () {
    return view('organizer.tasks');
});

Route::get('/organizer/eventos', function () {
    return view('organizer.events');
});

// Detail / edit pages for items
Route::get('/organizer/notas/{note}', function (App\Models\Note $note) {
    return view('organizer.note', compact('note'));
});

Route::get('/organizer/tareas/{task}', function (App\Models\Task $task) {
    return view('organizer.task', compact('task'));
});

Route::get('/organizer/eventos/{event}', function (App\Models\Event $event) {
    return view('organizer.event', compact('event'));
});

// React pages with Inertia
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/tasks', 'tasks')->name('tasks');
    Route::inertia('/task-list-preview', 'task-list-preview')->name('task-list-preview');
});

/*Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';*/