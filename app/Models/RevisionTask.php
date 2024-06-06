<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionTask extends Model
{
    use HasFactory;

    
    protected $table = 'revision_tasks';

    protected $fillable = [
        'role_name',
        'was',
        'new',
        'task_id',
        'created_by',
        'updated_by',
    ];
}
