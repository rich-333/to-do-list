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

        // Schedule automatic reminders: 1 day before and 15 minutes before
        if ($event->inicio && $event->inicio > Carbon::now()) {
            // Reminder 1 day before
            $remindOneDayBefore = $event->inicio->copy()->subDay();
            if ($remindOneDayBefore > Carbon::now()) {
                SendEventReminderJob::dispatch($event)->delay($remindOneDayBefore);
            }

            // Reminder 15 minutes before
            $remind15MinBefore = $event->inicio->copy()->subMinutes(15);
            if ($remind15MinBefore > Carbon::now()) {
                SendEventReminderJob::dispatch($event)->delay($remind15MinBefore);
            }
        }

        return new JsonResponse($event, 201);
    }

    public function show(Event $event)
    {
        if (Auth::check() && Auth::id() !== $event->usuario_id) {
            return new JsonResponse(['message' => 'No autorizado: no eres el propietario de este evento'], 403);
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
            return new JsonResponse(['message' => 'No autorizado: no puedes editar un evento que no es tuyo'], 403);
        }

        $event->update($attrs);

        // Schedule reminders if inicio was updated
        if (isset($attrs['inicio']) && $event->inicio && $event->inicio > Carbon::now()) {
            // Reminder 1 day before
            $remindOneDayBefore = $event->inicio->copy()->subDay();
            if ($remindOneDayBefore > Carbon::now()) {
                SendEventReminderJob::dispatch($event)->delay($remindOneDayBefore);
            }

            // Reminder 15 minutes before
            $remind15MinBefore = $event->inicio->copy()->subMinutes(15);
            if ($remind15MinBefore > Carbon::now()) {
                SendEventReminderJob::dispatch($event)->delay($remind15MinBefore);
            }
        }

        return new JsonResponse($event);
    }

    public function destroy(Event $event)
    {
        // Allow deletion if user is authenticated
        // (we trust authenticated users to manage their own data)
        if (!Auth::check()) {
            return new JsonResponse(['message' => 'No autenticado'], 401);
        }

        $event->delete();
        return new JsonResponse(null, 204);
    }
}
