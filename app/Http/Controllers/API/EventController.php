<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Event;
use App\Jobs\SendEventReminderJob;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? $request->query('usuario_id');
        $query = Event::query();
        if ($userId) $query->where('usuario_id', $userId);

        return new JsonResponse($query->latest()->paginate(25));
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'usuario_id' => 'sometimes|integer|exists:users,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'inicio' => 'required|date',
            'fin' => 'nullable|date|after_or_equal:inicio',
            'ubicacion' => 'nullable|string|max:255',
            'fecha_recordatorio' => 'nullable|date',
        ]);

        $attrs['usuario_id'] = $request->user()?->id ?? ($attrs['usuario_id'] ?? null);
        if (empty($attrs['usuario_id'])) {
            return new JsonResponse(['message' => 'usuario_id requerido para peticiones sin auth'], 422);
        }

        $event = Event::create($attrs);

        // schedule reminder job if provided
        if (!empty($event->fecha_recordatorio) && $event->fecha_recordatorio > Carbon::now()) {
            // schedule using the job dispatcher (still uses Laravel queue infra at runtime)
            SendEventReminderJob::dispatch($event)->delay($event->fecha_recordatorio);
        }

        return new JsonResponse($event, 201);
    }

    public function show(Event $event)
    {
        if (Auth::check() && Auth::id() !== $event->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        return new JsonResponse($event);
    }

    public function update(Request $request, Event $event)
    {
        $attrs = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'inicio' => 'nullable|date',
            'fin' => 'nullable|date|after_or_equal:inicio',
            'ubicacion' => 'nullable|string|max:255',
            'fecha_recordatorio' => 'nullable|date',
        ]);

        if (Auth::check() && Auth::id() !== $event->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $event->update($attrs);

        if (!empty($event->fecha_recordatorio) && $event->fecha_recordatorio > Carbon::now()) {
            SendEventReminderJob::dispatch($event)->delay($event->fecha_recordatorio);
        }

        return new JsonResponse($event);
    }

    public function destroy(Event $event)
    {
        if (Auth::check() && Auth::id() !== $event->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $event->delete();
        return new JsonResponse(null, 204);
    }
}
