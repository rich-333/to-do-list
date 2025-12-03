<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property int $usuario_id
 * @property string $titulo
 * @property string|null $descripcion
 * @property string|null $prioridad
 * @property \Illuminate\Support\Carbon|null $fecha_limite
 * @property string|null $estado
 * @property array|null $etiquetas
 * @property \Illuminate\Support\Carbon|null $fecha_completada
 * @property int|null $user_id    # alias English
 * @property string|null $title   # alias English -> titulo
 * @property string|null $content # alias English -> descripcion
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static Task create(array $attributes = [])
 * @method bool update(array $attributes = [])
 */
class Task extends Model
{
    use HasFactory;


    protected $fillable = [
        'usuario_id',
        'titulo',
        'conjunto',
        'descripcion',
        'prioridad',
        'fecha_limite',
        'estado',
        'etiquetas',
        'subtareas',
        'fecha_completada'
    ];

    protected $casts = [
        'etiquetas' => 'array',
        'subtareas' => 'array',
        'fecha_limite' => 'datetime',
        'fecha_completada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
