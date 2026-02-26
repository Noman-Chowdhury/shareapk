<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'package_name',
        'description',
        'icon_url',
    ];

    // A project has many builds
    public function builds()
    {
        return $this->hasMany(Build::class);
    }

    // Get the latest build
    public function latestBuild()
    {
        return $this->hasOne(Build::class)->latestOfMany();
    }

    // A project has many tasks through builds
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, Build::class);
    }

    // A project has many feedbacks through builds
    public function feedbacks()
    {
        return $this->hasManyThrough(Feedback::class, Build::class);
    }
}
