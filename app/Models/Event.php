<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Event
 *
 * @property int $id
 * @property int $usuario_id
 * @property string $titulo
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $inicio
 * @property \Illuminate\Support\Carbon|null $fin
 * @property \Illuminate\Support\Carbon|null $fecha_recordatorio
 * @property int|null $user_id                 # alias English
 * @property \Illuminate\Support\Carbon|null $reminder_at # alias English
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static Event create(array $attributes = [])
 * @method bool update(array $attributes = [])
 */
class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'titulo',
        'descripcion',
        'inicio',
        'fin',
        'ubicacion',
        'fecha_recordatorio',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'fecha_recordatorio' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
