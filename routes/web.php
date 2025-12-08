<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthJSONController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;

use App\Models\Note;
use App\Models\Task;
use App\Models\Event;
use App\Http\Controllers\API\NoteController;

Route::get('/', function () {
    $notas = Note::query()->latest()->take(8)->get();
    $tareas = Task::query()->latest()->take(8)->get();
    // Get upcoming events (future dates) ordered by inicio ascending, fallback to past events if none upcoming
    $eventos = Event::query()
        ->where('inicio', '>=', now())
        ->orderBy('inicio', 'asc')
        ->take(8)
        ->get();
    if ($eventos->isEmpty()) {
        $eventos = Event::query()->orderBy('inicio', 'desc')->take(8)->get();
    }
    
    // Convert eventos to array format for JavaScript
    $jsEvents = $eventos->map(function($ev) {
        return [
            'id' => $ev->id,
            'titulo' => $ev->titulo,
            'descripcion' => $ev->descripcion,
            'inicio' => $ev->inicio->toIso8601String(),
            'fin' => $ev->fin ? $ev->fin->toIso8601String() : null,
            'color' => $ev->color,
            'usuario_id' => $ev->usuario_id,
        ];
    })->toArray();
    
    return view('organizer.index-refactored', compact('notas','tareas','eventos','jsEvents'));
});

Route::get('/users', [AuthController::class, 'obtenerUsuarios']);

// Rutas de autenticación JSON
Route::post('/register', [AuthJSONController::class, 'register']);
Route::post('/login', [AuthJSONController::class, 'login']);
Route::post('/logout', [AuthJSONController::class, 'logout']);

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

// API routes para usuario
Route::get('/api/user', function (Request $request) {
    if ($request->user()) {
        return response()->json($request->user());
    }
    return response()->json(null, 401);
});

Route::put('/api/user', function (Request $request) {
    if (!$request->user()) {
        return response()->json(['message' => 'No autenticado'], 401);
    }
    
    $user = $request->user();
    $user->name = $request->input('name') ?? $user->name;
    $user->email = $request->input('email') ?? $user->email;
    $user->save();
    return response()->json($user);
})->middleware('auth');

// React pages with Inertia
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('/tasks', 'tasks')->name('tasks');
    Route::inertia('/task-list-preview', 'task-list-preview')->name('task-list-preview');
});

// Task lists (persisted) endpoints used by organizer UI
use App\Http\Controllers\TaskListController;

Route::middleware('auth')->group(function () {
    // Sugerir items con IA PRIMERO (antes de rutas con parámetros)
    Route::post('/ai/suggest-items', [TaskListController::class, 'suggestItems']);

    // Exponer endpoints IA para la vista de edición de notas usando sesión (auth)
    // Esto evita la necesidad de llamar a los endpoints API protegidos por Sanctum desde páginas blade.
    Route::post('/api/v1/notes/{note}/ai/suggest', [NoteController::class, 'suggest']);
    Route::post('/api/v1/notes/{note}/ai/analyze', [NoteController::class, 'analyze']);
    Route::post('/api/v1/notes/{note}/ai/expand', [NoteController::class, 'expand']);
    Route::post('/api/v1/notes/{note}/ai/summarize-content', [NoteController::class, 'summarizeContent']);
    Route::post('/api/v1/notes/{note}/to-event', [NoteController::class, 'toEvent']);
    Route::post('/api/v1/notes/{note}/to-task', [NoteController::class, 'toTask']);

    // Luego el resto
    Route::get('/task-lists', [TaskListController::class, 'indexJson']);
    Route::post('/task-lists', [TaskListController::class, 'store']);
    Route::put('/task-lists/{taskList}', [TaskListController::class, 'update']);
    Route::delete('/task-lists/{taskList}', [TaskListController::class, 'destroy']);

    Route::post('/task-lists/{taskList}/items', [TaskListController::class, 'addItem']);
    Route::put('/task-lists/{taskList}/items/{item}', [TaskListController::class, 'updateItem']);

    // Búsqueda semántica
    // Search route removed by request
});

/*Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';*/