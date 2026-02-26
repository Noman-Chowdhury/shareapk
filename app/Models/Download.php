<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    public $timestamps = true; // Only created_at needed really, but keep both

    protected $fillable = [
        'build_id',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    // Download belongs to a build
    public function build()
    {
        return $this->belongsTo(Build::class);
    }

    // Who downloaded (null = external/anonymous)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Which share link was used (if any)
    public function shareLink()
    {
        return $this->belongsTo(ShareLink::class);
    }
}
