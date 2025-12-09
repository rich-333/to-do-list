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
        'context',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(TaskListItem::class)->orderBy('order');
    }

    protected static function booted()
    {
        // Ensure items are deleted when a list is deleted (defensive: DB may not enforce FK cascades in some environments)
        static::deleting(function (TaskList $list) {
            $list->items()->delete();
        });
    }
}
