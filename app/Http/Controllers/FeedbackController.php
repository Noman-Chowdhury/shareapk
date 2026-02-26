<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\Feedback;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['author', 'build.project'])->latest()->get();
        return \Inertia\Inertia::render('Feedback/Index', [
            'feedbacks' => $feedbacks,
        ]);
    }

    public function store(Request $request, Build $build)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:Bug,Feature,Improvement',
            'severity' => 'nullable|in:Critical,High,Medium,Low',
            'device_model' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:255',
            'screen_size' => 'nullable|string|max:255',
        ]);

        $feedback = $build->feedbacks()->create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'severity' => $validated['severity'] ?? null,
            'status' => 'Open',
            'device_model' => $validated['device_model'] ?? null,
            'os_version' => $validated['os_version'] ?? null,
            'screen_size' => $validated['screen_size'] ?? null,
            'attachments' => [],
        ]);

        // Notify build uploader
        if ($build->user_id) {
            $build->uploader->notify(new GeneralNotification(
                "New {$feedback->type} Reported",
                "New feedback on build v{$build->version_name}: {$feedback->title}",
                route('builds.show', $build->id)
            ));
        }

        // Notify Admins
        $admins = User::role('Admin')->get();
        Notification::send($admins, new GeneralNotification(
            "Global Feedback: {$feedback->title}",
            "User " . auth()->user()->name . " reported a {$feedback->type} on {$build->project->name}.",
            route('feedback.index')
        ));

        ActivityLog::log('Feedback Submitted', "Reported {$feedback->type} on build v{$build->version_name}", $feedback);

        return back()->with('success', 'Feedback submitted successfully.');
    }

    public function update(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:Bug,Feature,Improvement',
            'severity' => 'nullable|in:Critical,High,Medium,Low',
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
            'device_model' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:255',
            'screen_size' => 'nullable|string|max:255',
        ]);

        $feedback->update($validated);

        return back()->with('success', 'Feedback updated.');
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return back()->with('success', 'Feedback deleted.');
    }
}
