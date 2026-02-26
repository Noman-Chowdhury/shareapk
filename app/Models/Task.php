<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'build_id',
        'creator_id',
        'assignee_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'attachments',
    ];

    protected $casts = [
        'due_date'    => 'date',
        'attachments' => 'array',
    ];

    // Task belongs to a build
    public function build()
    {
        return $this->belongsTo(Build::class);
    }

    // Task created by
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Task assigned to
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    // Polymorphic comments on a task
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
