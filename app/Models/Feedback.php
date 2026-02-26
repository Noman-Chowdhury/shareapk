<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'build_id',
        'user_id',
        'assignee_id',
        'title',
        'description',
        'type',
        'severity',
        'status',
        'device_model',
        'os_version',
        'screen_size',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function build()
    {
        return $this->belongsTo(Build::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
