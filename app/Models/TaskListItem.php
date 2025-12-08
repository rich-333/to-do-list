<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $task_list_id
 * @property string $name
 * @property bool $completed
 * @property int $order
 */
class TaskListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_list_id',
        'name',
        'completed',
        'order',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function list()
    {
        return $this->belongsTo(TaskList::class, 'task_list_id');
    }
}
