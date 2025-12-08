<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 */
class TaskList extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(TaskListItem::class)->orderBy('order');
    }
}
