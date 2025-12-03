<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;
use App\Models\Task;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? $request->query('usuario_id');
        $query = Note::query();
        if ($userId) $query->where('usuario_id', $userId);
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
            'contenido' => 'required|string',
            'etiquetas' => 'nullable|array',
            'color' => 'nullable|string|max:20',
        ]);

        $attrs['usuario_id'] = $request->user()?->id ?? ($attrs['usuario_id'] ?? null);

        if (empty($attrs['usuario_id'])) {
            return new JsonResponse(['message' => 'usuario_id requerido para peticiones sin auth'], 422);
        }

        $note = Note::create($attrs);
        return new JsonResponse($note, 201);
    }

    public function show(Note $note)
    {
        // permitir sólo al propietario si hay sesión
        if (Auth::check() && Auth::id() !== $note->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        return new JsonResponse($note);
    }

    public function update(Request $request, Note $note)
    {
        $attrs = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'contenido' => 'sometimes|string',
            'etiquetas' => 'nullable|array',
            'color' => 'nullable|string|max:20',
        ]);

        if (Auth::check() && Auth::id() !== $note->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $note->update($attrs);
        return new JsonResponse($note);
    }

    public function destroy(Note $note)
    {
        if (Auth::check() && Auth::id() !== $note->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $note->delete();
        return new JsonResponse(null, 204);
    }

    // --- Optional AI-ish endpoints (stubs) ---
    public function summarize(Note $note)
    {
        // Simple stub that returns the first 120 chars
        $summary = substr(strip_tags($note->contenido), 0, 120);
        return new JsonResponse(['summary' => $summary]);
    }

    public function transform(Request $request)
    {
        $attrs = $request->validate([
            'text' => 'required|string',
            'operation' => 'sometimes|string|in:uppercase,lowercase,striphtml',
        ]);

        $text = $attrs['text'];
        if (($attrs['operation'] ?? null) === 'uppercase') $text = strtoupper($text);
        if (($attrs['operation'] ?? null) === 'lowercase') $text = strtolower($text);
        if (($attrs['operation'] ?? null) === 'striphtml') $text = strip_tags($text);

        return new JsonResponse(['result' => $text]);
    }

    public function toTask(Note $note, Request $request)
    {
        $titulo = $note->titulo;
        $task = Task::create([
            'usuario_id' => $note->usuario_id,
            'titulo' => $titulo,
            'descripcion' => $note->contenido,
            'prioridad' => $request->input('prioridad', 'media'),
            'estado' => 'pendiente',
            'etiquetas' => $note->etiquetas,
        ]);

        return new JsonResponse($task, 201);
    }
}
