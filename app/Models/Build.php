<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Build extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'user_id',
        'version_name',
        'version_code',
        'build_type',
        'release_notes',
        'file_path',
        'file_size',
        'status',
        'approved_by',
        'approval_remarks',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'file_size'    => 'integer',
    ];

    // Build belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Uploader
    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Approver
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // A build has many feedback items
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    // A build has many tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // A build has many downloads
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    // A build has many share links
    public function shareLinks()
    {
        return $this->hasMany(ShareLink::class);
    }

    // Polymorphic comments on a build
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Human-readable file size
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
