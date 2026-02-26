<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShareLink extends Model
{
    protected $fillable = [
        'build_id',
        'user_id',
        'token',
        'download_limit',
        'download_count',
        'expires_at',
        'password',
    ];

    protected $casts = [
        'expires_at'     => 'datetime',
        'download_limit' => 'integer',
        'download_count' => 'integer',
    ];

    // Auto-generate token before creating
    protected static function booted()
    {
        static::creating(function ($link) {
            if (empty($link->token)) {
                $link->token = Str::random(32);
            }
        });
    }

    // ShareLink belongs to a build
    public function build()
    {
        return $this->belongsTo(Build::class);
    }

    // Created by a user
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Check if the link is still usable
    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->download_limit !== null && $this->download_count >= $this->download_limit) {
            return false;
        }
        return true;
    }
}
