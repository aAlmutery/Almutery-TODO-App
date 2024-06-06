<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLogs extends Model
{
    use HasFactory;

    
    protected $table = 'task_logs';

    protected $fillable = [
        'role_name',
        'name',
        'user_name',
        'task_id',
        'created_by',
        'updated_by'
    ];
}
