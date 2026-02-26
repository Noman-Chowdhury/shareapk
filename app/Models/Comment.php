<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'user_id',
        'body',
        'attachment_path',
        'attachment_name',
    ];

    // Polymorphic relationship — can attach to Build, Task, or Feedback
    public function commentable()
    {
        return $this->morphTo();
    }

    // Comment written by a user
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

     // Comment written by a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
