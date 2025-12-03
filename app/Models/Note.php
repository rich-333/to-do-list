<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Note
 *
 * @property int $id
 * @property int $usuario_id
 * @property string $titulo
 * @property string $contenido
 * @property array|null $etiquetas
 * @property int|null $user_id   # alias English
 * @property string|null $title  # alias English -> titulo
 * @property string|null $content # alias English -> contenido
 * @property string|null $color
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Note query()
 * @method static Note create(array $attributes = [])
 * @method bool update(array $attributes = [])
 */
class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'titulo',
        'contenido',
        'etiquetas',
        'color',
    ];

    protected $casts = [
        'etiquetas' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
