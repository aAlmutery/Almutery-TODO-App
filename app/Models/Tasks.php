<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'parent',
        'status',
        'assign_to',
        'due_date',
    ];

    public function SubTask(){
        return $this->hasMany(Tasks::class, 'parent', 'id');
    }

    public function parent(){
        return $this->belongsTo(Tasks::class, 'parent', 'id');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function status(){
        return $this->belongsTo(StatusList::class, 'status', 'id');
    }    

    public function assignTo(){
        return $this->belongsTo(User::class, 'assign_to', 'id');
    }    
    
}
