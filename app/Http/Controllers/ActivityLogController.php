<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Only Admins should see the full logs
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return Inertia::render('Admin/ActivityLogs', [
            'logs' => $logs
        ]);
    }
}
