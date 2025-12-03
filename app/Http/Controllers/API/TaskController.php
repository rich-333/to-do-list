<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? $request->query('usuario_id');
        $query = Task::query();
        if ($userId) $query->where('usuario_id', $userId);
        if ($request->filled('prioridad')) $query->where('prioridad', $request->query('prioridad'));
        if ($request->filled('estado')) $query->where('estado', $request->query('estado'));
        if ($request->filled('etiquetas')) {
            $tags = array_filter(explode(',', $request->query('etiquetas')));
            $query->whereJsonContains('etiquetas', $tags);
        }

        return new JsonResponse($query->latest()->paginate(25));
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'usuario_id' => 'sometimes|integer|exists:users,id',
            'titulo' => 'required|string|max:255',
            'conjunto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => 'nullable|in:baja,media,alta',
            'fecha_limite' => 'nullable|date',
            'estado' => 'nullable|in:pendiente,en_progreso,completada,cancelada',
            'etiquetas' => 'nullable|array',
            'subtareas' => 'nullable|array',
        ]);

        $attrs['usuario_id'] = $request->user()?->id ?? ($attrs['usuario_id'] ?? null);
        if (empty($attrs['usuario_id'])) {
            return new JsonResponse(['message' => 'usuario_id requerido para peticiones sin auth'], 422);
        }

        $task = Task::create($attrs);
        return new JsonResponse($task, 201);
    }

    public function show(Task $task)
    {
        if (Auth::check() && Auth::id() !== $task->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        return new JsonResponse($task);
    }

    public function update(Request $request, Task $task)
    {
        $attrs = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'conjunto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'prioridad' => 'nullable|in:baja,media,alta',
            'fecha_limite' => 'nullable|date',
            'estado' => 'nullable|in:pendiente,en_progreso,completada,cancelada',
            'etiquetas' => 'nullable|array',
            'subtareas' => 'nullable|array',
        ]);

        if (Auth::check() && Auth::id() !== $task->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $task->update($attrs);
        return new JsonResponse($task);
    }

    public function destroy(Task $task)
    {
        if (Auth::check() && Auth::id() !== $task->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $task->delete();
        return new JsonResponse(null, 204);
    }

    public function complete(Task $task)
    {
        if (Auth::check() && Auth::id() !== $task->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }
        $task->estado = 'completada';
        $task->fecha_completada = \Illuminate\Support\Carbon::now();
        $task->save();

        return new JsonResponse($task);
    }

    public function updateStatus(Request $request, Task $task)
    {
        if (Auth::check() && Auth::id() !== $task->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $attrs = $request->validate([
            'estado' => 'required|in:pendiente,en_progreso,completada,cancelada',
        ]);

        $task->estado = $attrs['estado'];
        if ($attrs['estado'] === 'completada') {
            $task->fecha_completada = \Illuminate\Support\Carbon::now();
        } else {
            $task->fecha_completada = null;
        }
        $task->save();

        return new JsonResponse($task);
    }
}
