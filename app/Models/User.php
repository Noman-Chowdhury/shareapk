<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Builds uploaded by this user
    public function builds()
    {
        return $this->hasMany(Build::class);
    }

    // Feedback submitted by this user
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    // Tasks assigned to this user
    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    // Tasks created by this user
    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    // Comments written by this user
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Downloads tracked for this user
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }
}
